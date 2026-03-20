<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Spatie\SlackAlerts\Facades\SlackAlert;

class Handler extends ExceptionHandler
{
    public function report(Throwable $exception)
    {
        // Send Slack alert for any exception
        SlackAlert::message("❌ Exception Occurred: " . $exception->getMessage())
            ->danger();

        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}