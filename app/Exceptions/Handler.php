<?php

namespace App\Exceptions;

use App\Listeners\LoggingListener;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * The unique incident ID code
     *
     * @var string|bool
     */
    protected $incidentCode = false;

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);

        $this->incidentCode = str_random();

        $listener = $this->container->make(LoggingListener::class);

        $listener->events->map(function (MessageLogged $logged) {
            $logged->context = collect($logged->context)->map(function ($item) {
                if ($item instanceof \JsonSerializable) {
                    return $item;
                }

                return (string) $item;
            });
            return $logged;
        });

        Storage::disk('local')->put("incident\\{$this->incidentCode}.json", $listener->events->toJson(JSON_PRETTY_PRINT));
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Render the given HttpException.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpException $e)
    {
        $this->registerErrorViewPaths();

        if (view()->exists($view = "errors::{$e->getStatusCode()}")) {
            return response()->view($view, [
                'errors' => new ViewErrorBag,
                'exception' => $e,
                'incidentCode' => $this->incidentCode ?? false,
            ], $e->getStatusCode(), $e->getHeaders());
        }

        return $this->convertExceptionToResponse($e);
    }
}
