<?php

$requestUrl = $this->baseUrl . '/path';
$headers = array('Content-Type' => 'application/x-www-form-urlencoded');
$bodySerializationContext = \JMS\Serializer\SerializationContext::create();
$bodySerializationContext->setGroups(array('test' => 'group'));
$bodySerializationContext->setVersion(1);
$bodySerializationContext->setSerializeNull(1);
$bodySerializationContext->enableMaxDepthChecks();
$bodySerializationContext->setAttribute('foo', 'bar');
$serializedBody = $this->serializer->serialize($body, 'json', $bodySerializationContext);
$bodyArray = json_decode($serializedBody, true);
$bodyArray = \Tebru\Retrofit\Generation\Manipulator\QueryManipulator::boolToString($bodyArray);
$body = http_build_query($bodyArray);
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
$retrofitResponse = new \Tebru\Retrofit\Http\Response($response, 'array', $this->serializer, array());
$return = $retrofitResponse->body();
$returnEvent = new \Tebru\Retrofit\Event\ReturnEvent($return);
$this->eventDispatcher->dispatch('retrofit.return', $returnEvent);
return $returnEvent->getReturn();