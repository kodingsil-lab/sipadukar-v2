<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('sanitize_external_dokumen_link')) {
	function sanitize_external_dokumen_link(?string $url): string
	{
		$url = trim((string) $url);
		if ($url === '' || str_contains($url, "\0")) {
			return '';
		}

		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			return '';
		}

		$parts = parse_url($url);
		if (! is_array($parts)) {
			return '';
		}

		$scheme = strtolower((string) ($parts['scheme'] ?? ''));
		$host = trim((string) ($parts['host'] ?? ''));
		if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
			return '';
		}

		return $url;
	}
}

if (! function_exists('is_safe_external_dokumen_link')) {
	function is_safe_external_dokumen_link(?string $url): bool
	{
		return sanitize_external_dokumen_link($url) !== '';
	}
}

if (! function_exists('dokumen_preview_embed_link')) {
	function dokumen_preview_embed_link(?string $url): string
	{
		$safeUrl = sanitize_external_dokumen_link($url);
		if ($safeUrl === '') {
			return '';
		}

		$parts = parse_url($safeUrl);
		if (! is_array($parts)) {
			return $safeUrl;
		}

		$host = strtolower((string) ($parts['host'] ?? ''));
		$path = (string) ($parts['path'] ?? '');
		$query = (string) ($parts['query'] ?? '');

		if (str_contains($host, 'drive.google.com')) {
			if (preg_match('#/file/d/([^/]+)#', $path, $matches)) {
				return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
			}

			parse_str($query, $queryParams);
			$id = trim((string) ($queryParams['id'] ?? ''));
			if ($id !== '') {
				return 'https://drive.google.com/file/d/' . $id . '/preview';
			}
		}

		if (str_contains($host, 'docs.google.com') && preg_match('#/(document|spreadsheets|presentation)/d/([^/]+)#', $path, $matches)) {
			return 'https://docs.google.com/' . $matches[1] . '/d/' . $matches[2] . '/preview';
		}

		return $safeUrl;
	}
}
