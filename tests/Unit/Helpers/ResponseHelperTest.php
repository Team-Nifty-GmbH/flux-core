<?php

use FluxErp\Helpers\ResponseHelper;

test('ok returns 200 json response', function (): void {
    $response = ResponseHelper::ok('Success', ['key' => 'value']);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getData(true))->toMatchArray([
        'status' => 200,
        'statusMessage' => 'Success',
        'data' => ['key' => 'value'],
    ]);
});

test('created returns 201 json response', function (): void {
    $response = ResponseHelper::created('Created', ['id' => 1]);

    expect($response->getStatusCode())->toBe(201);
    expect($response->getData(true)['status'])->toBe(201);
});

test('noContent returns 204', function (): void {
    $response = ResponseHelper::noContent();

    expect($response->getStatusCode())->toBe(204);
});

test('badRequest returns 400', function (): void {
    $response = ResponseHelper::badRequest('Bad Request');

    expect($response->getStatusCode())->toBe(400);
});

test('notFound returns 404', function (): void {
    $response = ResponseHelper::notFound('Not Found');

    expect($response->getStatusCode())->toBe(404);
});

test('locked returns 423', function (): void {
    $response = ResponseHelper::locked('Resource Locked');

    expect($response->getStatusCode())->toBe(423);
});

test('unprocessableEntity returns 422 with errors', function (): void {
    $response = ResponseHelper::unprocessableEntity('Validation failed', ['field' => ['required']]);

    expect($response->getStatusCode())->toBe(422);
    expect($response->getData(true)['errors'])->toBe(['field' => ['required']]);
});

test('conflict returns 409', function (): void {
    $response = ResponseHelper::conflict('Conflict');

    expect($response->getStatusCode())->toBe(409);
});

test('createArrayResponse for success', function (): void {
    $result = ResponseHelper::createArrayResponse(200, ['id' => 1], statusMessage: 'OK');

    expect($result)->toMatchArray([
        'status' => 200,
        'statusMessage' => 'OK',
        'data' => ['id' => 1],
    ]);
});

test('createArrayResponse for error', function (): void {
    $result = ResponseHelper::createArrayResponse(422, ['field' => 'required']);

    expect($result['status'])->toBe(422);
    expect($result)->toHaveKey('errors');
});

test('createArrayResponse for 204 has no data', function (): void {
    $result = ResponseHelper::createArrayResponse(204);

    expect($result)->toBe(['status' => 204]);
});
