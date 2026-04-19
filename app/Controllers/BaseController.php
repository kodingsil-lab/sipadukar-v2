<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['sipadukar'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $timezone = app_setting('app_timezone', config('App')->appTimezone ?? 'Asia/Jakarta');
        if (is_string($timezone) && $timezone !== '') {
            try {
                date_default_timezone_set($timezone);
            } catch (\Throwable $e) {
                date_default_timezone_set('Asia/Jakarta');
            }
        }
    }
}
