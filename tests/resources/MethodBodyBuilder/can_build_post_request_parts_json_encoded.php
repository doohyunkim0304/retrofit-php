<?php

$requestUrl = $this->baseUrl . '/path';
$headers = array();
$body = json_encode(array('foo' => 'bar'));
$request = new \GuzzleHttp\Psr7\Request('POST', $requestUrl, $headers, $body);
$beforeSendEvent = new \Tebru\Retrofit\Event\BeforeSendEvent($request);
$this->eventDispatcher->dispatch('retrofit.beforeSend', $beforeSendEvent);
$request = $beforeSendEvent->getRequest();
try {
    $response = $this->client->send($request);
} catch (\Exception $exception) {
    $apiExceptionEvent = new \Tebru\Retrofit\Event\ApiExceptionEvent($exception, $request);
    $this->eventDispatcher->dispatch('retrofit.apiException', $apiExceptionEvent);
    $exception = $apiExceptionEvent->getException();
    throw new \Tebru\Retrofit\Exception\RetrofitApiException(get_class($this), $exception->getMessage(), $exception->getCode(), $exception);
}
$afterSendEvent = new \Tebru\Retrofit\Event\AfterSendEvent($request, $response);
$this->eventDispatcher->dispatch('retrofit.afterSend', $afterSendEvent);
$response = $afterSendEvent->getResponse();
$retrofitResponse = new \Tebru\Retrofit\Http\Response($response, 'Tebru\\Retrofit\\Test\\Mock\\MockUser', $this->serializer, array('serializeNull' => true));
$return = $retrofitResponse->body();
$returnEvent = new \Tebru\Retrofit\Event\ReturnEvent($return);
$this->eventDispatcher->dispatch('retrofit.return', $returnEvent);
return $returnEvent->getReturn();
