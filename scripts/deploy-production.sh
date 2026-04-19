#!/usr/bin/env bash

set -Eeuo pipefail

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOMAIN_NAME="${DOMAIN_NAME:-sipadukar.demo.sil.web.id}"
APP_ROOT="${APP_ROOT:-/home/loaunisa/apps/sipadukar/sipadukar-app}"
PUBLIC_ROOT="${PUBLIC_ROOT:-/home/loaunisa/sipadukar.demo.sil.web.id}"
SHARED_ENV="${SHARED_ENV:-/home/loaunisa/apps/sipadukar/shared/.env}"
SHARED_ROOT="${SHARED_ROOT:-$(dirname "${SHARED_ENV}")}"
PHP_BIN="${PHP_BIN:-php}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-1}"
# Seeder default dimatikan untuk mencegah data duplikat pada deploy rutin.
RUN_SEEDERS="${RUN_SEEDERS:-0}"
SEEDER_CLASS="${SEEDER_CLASS:-DatabaseSeeder}"

log() {
    printf '[deploy] %s\n' "$1"
}

fail() {
    printf '[deploy][error] %s\n' "$1" >&2
    exit 1
}

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || fail "Perintah '$1' tidak tersedia di server."
}

has_cmd() {
    command -v "$1" >/dev/null 2>&1
}

php_function_disabled() {
    local function_name="$1"
    local disabled_functions

    disabled_functions="$("${PHP_BIN}" -r 'echo (string) ini_get("disable_functions");' 2>/dev/null || true)"
    disabled_functions=",${disabled_functions// /},"

    [[ "${disabled_functions}" == *",${function_name},"* ]]
}

run_composer() {
    if command -v composer >/dev/null 2>&1; then
        composer "$@"
        return
    fi

    if [ -f "${APP_ROOT}/composer.phar" ]; then
        "${PHP_BIN}" "${APP_ROOT}/composer.phar" "$@"
        return
    fi

    if [ -f "${SOURCE_DIR}/composer.phar" ]; then
        "${PHP_BIN}" "${SOURCE_DIR}/composer.phar" "$@"
        return
    fi

    fail "Composer tidak ditemukan. Install composer atau upload composer.phar ke server."
}

need_cmd "${PHP_BIN}"

sync_dir() {
    local source_dir="$1"
    local target_dir="$2"
    shift 2
    local rsync_excludes=("$@")

    if has_cmd rsync; then
        local rsync_args=(-av --delete)
        for item in "${rsync_excludes[@]}"; do
            rsync_args+=(--exclude="$item")
        done

        rsync "${rsync_args[@]}" "${source_dir}/" "${target_dir}/"
        return
    fi

    log "rsync tidak tersedia, menggunakan fallback sinkronisasi dengan tar."
    mkdir -p "${target_dir}"

    local tar_excludes=()
    for item in "${rsync_excludes[@]}"; do
        tar_excludes+=(--exclude="$item")
    done

    # Hapus isi target untuk meniru perilaku --delete.
    find "${target_dir}" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
    (cd "${source_dir}" && tar -cf - "${tar_excludes[@]}" .) | (cd "${target_dir}" && tar -xf -)
}

run_spark() {
    "${PHP_BIN}" spark "$@"
}

log "Menyiapkan folder deploy"
mkdir -p "${APP_ROOT}" "${PUBLIC_ROOT}" "${SHARED_ROOT}"
mkdir -p \
    "${APP_ROOT}/writable/cache" \
    "${APP_ROOT}/writable/debugbar" \
    "${APP_ROOT}/writable/logs" \
    "${APP_ROOT}/writable/session" \
    "${APP_ROOT}/writable/uploads" \
    "${PUBLIC_ROOT}/uploads"

if [ ! -f "${SHARED_ENV}" ]; then
    if [ -f "${SOURCE_DIR}/.env.production.example" ]; then
        cp "${SOURCE_DIR}/.env.production.example" "${SHARED_ENV}"
        log "Template .env production disalin ke ${SHARED_ENV}. Lengkapi kredensial database sebelum akses publik."
    else
        fail "${SHARED_ENV} belum ada dan template .env production tidak ditemukan."
    fi
fi

log "Sinkronisasi source aplikasi ke ${APP_ROOT}"
sync_dir "${SOURCE_DIR}" "${APP_ROOT}" \
    ".git/" \
    ".github/" \
    ".vscode/" \
    ".env" \
    "writable/" \
    "public/uploads/" \
    "tests/" \
    "vendor/" \
    "phpunit.xml" \
    "phpunit.xml.dist" \
    "scripts/deploy-production.sh"

cp "${SHARED_ENV}" "${APP_ROOT}/.env"
chmod 640 "${APP_ROOT}/.env" || true

log "Sinkronisasi dokumen publik ke ${PUBLIC_ROOT}"
sync_dir "${SOURCE_DIR}/public" "${PUBLIC_ROOT}" "uploads/"

APP_PATHS_FILE="${APP_ROOT}/app/Config/Paths.php"
PUBLIC_INDEX_FILE="${PUBLIC_ROOT}/index.php"

[ -f "${APP_PATHS_FILE}" ] || fail "Paths.php tidak ditemukan di ${APP_PATHS_FILE}"
[ -f "${PUBLIC_INDEX_FILE}" ] || fail "index.php publik tidak ditemukan di ${PUBLIC_INDEX_FILE}"

log "Menyesuaikan front controller untuk struktur hosting"
if grep -q "require FCPATH \. '../app/Config/Paths.php';" "${PUBLIC_INDEX_FILE}"; then
    sed -i "s#require FCPATH \. '../app/Config/Paths.php';#require '${APP_ROOT}/app/Config/Paths.php';#" "${PUBLIC_INDEX_FILE}"
elif grep -q "require '.*app/Config/Paths.php';" "${PUBLIC_INDEX_FILE}"; then
    sed -E -i "s#require '.*app/Config/Paths.php';#require '${APP_ROOT}/app/Config/Paths.php';#" "${PUBLIC_INDEX_FILE}"
else
    fail "Tidak menemukan baris require Paths.php pada ${PUBLIC_INDEX_FILE}."
fi

log "Install dependency production"
cd "${APP_ROOT}"
run_composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

if [ "${RUN_MIGRATIONS}" = "1" ]; then
    log "Menjalankan migration"
    run_spark migrate --all --no-interaction
fi

if [ "${RUN_SEEDERS}" = "1" ]; then
    log "Menjalankan seeder sistem (${SEEDER_CLASS})"
    run_spark db:seed "${SEEDER_CLASS}" --no-interaction
fi

log "Membersihkan cache dan optimize aplikasi"
run_spark cache:clear || true
if php_function_disabled "passthru"; then
    log "Melewati 'php spark optimize' karena fungsi passthru() diblokir oleh hosting."
else
    run_spark optimize || true
fi

log "Mengatur permission folder writable dan uploads"
chmod -R 775 "${APP_ROOT}/writable" "${PUBLIC_ROOT}/uploads" || true

log "Deploy selesai"
log "Domain     : ${DOMAIN_NAME}"
log "App root   : ${APP_ROOT}"
log "Public root: ${PUBLIC_ROOT}"
log "Env file   : ${SHARED_ENV}"
