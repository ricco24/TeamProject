<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Application
 */



/**
 * Front Controller.
 *
 * @author     David Grudl
 *
 * @property-read array $requests
 * @property-read IPresenter $presenter
 * @property-read IRouter $router
 * @property-read IPresenterFactory $presenterFactory
 * @package Nette\Application
 */
class NApplication extends NObject
{
	/** @var int */
	public static $maxLoop = 20;

	/** @var bool enable fault barrier? */
	public $catchExceptions;

	/** @var string */
	public $errorPresenter;

	/** @var array of function(Application $sender); Occurs before the application loads presenter */
	public $onStartup;

	/** @var array of function(Application $sender, Exception $e = NULL); Occurs before the application shuts down */
	public $onShutdown;

	/** @var array of function(Application $sender, Request $request); Occurs when a new request is ready for dispatch */
	public $onRequest;

	/** @var array of function(Application $sender, IResponse $response); Occurs when a new response is received */
	public $onResponse;

	/** @var array of function(Application $sender, Exception $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @deprecated */
	public $allowedMethods;

	/** @var array of NPresenterRequest */
	private $requests = array();

	/** @var IPresenter */
	private $presenter;

	/** @var IHttpRequest */
	private $httpRequest;

	/** @var IHttpResponse */
	private $httpResponse;

	/** @var IPresenterFactory */
	private $presenterFactory;

	/** @var IRouter */
	private $router;



	public function __construct(IPresenterFactory $presenterFactory, IRouter $router, IHttpRequest $httpRequest, IHttpResponse $httpResponse)
	{
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$request = NULL;
		$repeatedError = FALSE;
		do {
			try {
				if (count($this->requests) > self::$maxLoop) {
					throw new NApplicationException('Too many loops detected in application life cycle.');
				}

				if (!$request) {
					$this->onStartup($this);

					$request = $this->router->match($this->httpRequest);
					if (!$request instanceof NPresenterRequest) {
						$request = NULL;
						throw new NBadRequestException('No route for HTTP request.');
					}

					if (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
						throw new NBadRequestException('Invalid request. Presenter is not achievable.');
					}
				}

				$this->requests[] = $request;
				$this->onRequest($this, $request);

				// Instantiate presenter
				$presenterName = $request->getPresenterName();
				try {
					$this->presenter = $this->presenterFactory->createPresenter($presenterName);
				} catch (NInvalidPresenterException $e) {
					throw new NBadRequestException($e->getMessage(), 404, $e);
				}

				$this->presenterFactory->getPresenterClass($presenterName);
				$request->setPresenterName($presenterName);
				$request->freeze();

				// Execute presenter
				$response = $this->presenter->run($request);
				if ($response) {
					$this->onResponse($this, $response);
				}

				// Send response
				if ($response instanceof NForwardResponse) {
					$request = $response->getRequest();
					continue;

				} elseif ($response instanceof IPresenterResponse) {
					$response->send($this->httpRequest, $this->httpResponse);
				}
				break;

			} catch (Exception $e) {
				// fault barrier
				$this->onError($this, $e);

				if (!$this->catchExceptions) {
					$this->onShutdown($this, $e);
					throw $e;
				}

				if ($repeatedError) {
					$e = new NApplicationException('An error occurred while executing error-presenter', 0, $e);
				}

				if (!$this->httpResponse->isSent()) {
					$this->httpResponse->setCode($e instanceof NBadRequestException ? $e->getCode() : 500);
				}

				if (!$repeatedError && $this->errorPresenter) {
					$repeatedError = TRUE;
					if ($this->presenter instanceof NPresenter) {
						try {
							$this->presenter->forward(":$this->errorPresenter:", array('exception' => $e));
						} catch (NAbortException $foo) {
							$request = $this->presenter->getLastCreatedRequest();
						}
					} else {
						$request = new NPresenterRequest(
							$this->errorPresenter,
							NPresenterRequest::FORWARD,
							array('exception' => $e)
						);
					}
					// continue

				} else { // default error handler
					if ($e instanceof NBadRequestException) {
						$code = $e->getCode();
					} else {
						$code = 500;
						NDebugger::log($e, NDebugger::ERROR);
					}
					require dirname(__FILE__) . '/templates/error.phtml';
					break;
				}
			}
		} while (1);

		$this->onShutdown($this, isset($e) ? $e : NULL);
	}



	/**
	 * Returns all processed requests.
	 * @return array of Request
	 */
	final public function getRequests()
	{
		return $this->requests;
	}



	/**
	 * Returns current presenter.
	 * @return IPresenter
	 */
	final public function getPresenter()
	{
		return $this->presenter;
	}



	/********************* services ****************d*g**/



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->router;
	}



	/**
	 * Returns presenter factory.
	 * @return IPresenterFactory
	 */
	public function getPresenterFactory()
	{
		return $this->presenterFactory;
	}



	/********************* request serialization ****************d*g**/



	/** @deprecated */
	function storeRequest($expiration = '+ 10 minutes')
	{
		return $this->presenter->storeRequest($expiration);
	}

	/** @deprecated */
	function restoreRequest($key)
	{
		return $this->presenter->restoreRequest($key);
	}

}
