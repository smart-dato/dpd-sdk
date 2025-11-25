<?php

use SmartDato\Dpd\Clients\ShipmentServiceClient;
use SmartDato\Dpd\DTOs\Label;
use SmartDato\Dpd\DTOs\ShipmentResponse;
use SmartDato\Dpd\Services\ShipmentService;

beforeEach(function () {
    $this->mockClient = Mockery::mock(ShipmentServiceClient::class);
    $this->config = [
        'defaults' => [
            'print_options' => [
                'outputFormat' => 'PDF',
                'paperFormat' => 'A4',
            ],
        ],
    ];
    $this->service = new ShipmentService($this->mockClient, $this->config);
});

afterEach(function () {
    Mockery::close();
});

it('can create a shipment', function () {
    $shipmentData = [
        'sender' => [
            'name' => 'John Doe',
            'street' => 'Main St',
            'houseNumber' => '123',
            'zipCode' => '12345',
            'city' => 'Berlin',
            'country' => 'DE',
        ],
        'recipient' => [
            'name' => 'Jane Smith',
            'street' => 'Second Ave',
            'houseNumber' => '456',
            'zipCode' => '54321',
            'city' => 'Hamburg',
            'country' => 'DE',
        ],
        'parcels' => [
            [
                'weight' => 2.5,
                'content' => 'Books',
            ],
        ],
    ];

    $mockResponse = (object) [
        'orderResult' => (object) [
            'shipmentResponses' => (object) [
                'parcelInformation' => (object) [
                    'parcelLabelNumber' => '123456789',
                ],
            ],
            'output' => (object) [
                'content' => 'base64encodedlabel',
                'format' => 'PDF',
            ],
        ],
    ];

    $this->mockClient->shouldReceive('storeOrders')
        ->once()
        ->andReturn($mockResponse);

    $response = $this->service->createShipment($shipmentData);

    expect($response)->toBeInstanceOf(ShipmentResponse::class)
        ->and($response->parcelNumber)->toBe('123456789')
        ->and($response->label)->toBeInstanceOf(Label::class)
        ->and($response->label->content)->toBe('base64encodedlabel')
        ->and($response->label->format)->toBe('PDF')
        ->and($response->trackingUrl)->toContain('123456789');
});

it('uses default print options when not provided', function () {
    $shipmentData = [
        'sender' => ['name' => 'Test'],
        'recipient' => ['name' => 'Test'],
        'parcels' => [],
    ];

    $mockResponse = (object) [
        'orderResult' => (object) [
            'shipmentResponses' => (object) [
                'parcelInformation' => (object) [
                    'parcelLabelNumber' => '123456789',
                ],
            ],
            'output' => (object) [
                'content' => 'label',
                'format' => 'PDF',
            ],
        ],
    ];

    $this->mockClient->shouldReceive('storeOrders')
        ->once()
        ->with(
            Mockery::on(function ($arg) {
                return isset($arg['printOption'][0]['outputFormat'])
                    && $arg['printOption'][0]['outputFormat'] === 'PDF';
            }),
            Mockery::any()
        )
        ->andReturn($mockResponse);

    $this->service->createShipment($shipmentData);
});

it('normalizes address fields correctly', function () {
    $shipmentData = [
        'sender' => [
            'name' => 'John Doe',
            'company' => 'Acme Corp',
            'street' => 'Main St',
            'houseNumber' => '123',
            'zipCode' => '12345',
            'city' => 'Berlin',
            'country' => 'DE',
            'email' => 'john@example.com',
            'phone' => '+49123456789',
        ],
        'recipient' => [
            'name1' => 'Jane Smith',
            'name2' => 'Smith Corp',
            'street' => 'Second Ave',
            'houseNo' => '456',
            'zipCode' => '54321',
            'city' => 'Hamburg',
            'country' => 'DE',
        ],
        'parcels' => [],
    ];

    $mockResponse = (object) [
        'orderResult' => (object) [
            'shipmentResponses' => (object) [
                'parcelInformation' => (object) [
                    'parcelLabelNumber' => '123456789',
                ],
            ],
            'output' => (object) [
                'content' => 'label',
                'format' => 'PDF',
            ],
        ],
    ];

    $this->mockClient->shouldReceive('storeOrders')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($orders) {
                $order = $orders[0];
                $sender = $order['generalShipmentData']['sender'];
                $recipient = $order['generalShipmentData']['recipient'];

                return $sender['name1'] === 'John Doe'
                    && $sender['name2'] === 'Acme Corp'
                    && $sender['houseNo'] === '123'
                    && $recipient['name1'] === 'Jane Smith'
                    && $recipient['name2'] === 'Smith Corp'
                    && $recipient['houseNo'] === '456';
            })
        )
        ->andReturn($mockResponse);

    $this->service->createShipment($shipmentData);
});

it('throws exception when response is invalid', function () {
    $shipmentData = [
        'sender' => ['name' => 'Test'],
        'recipient' => ['name' => 'Test'],
        'parcels' => [],
    ];

    $mockResponse = (object) [
        'orderResult' => (object) [
            'shipmentResponses' => (object) [],
        ],
    ];

    $this->mockClient->shouldReceive('storeOrders')
        ->once()
        ->andReturn($mockResponse);

    $this->service->createShipment($shipmentData);
})->throws(RuntimeException::class, 'Invalid shipment response from DPD');

it('uses custom print options when provided', function () {
    $shipmentData = [
        'sender' => ['name' => 'Test'],
        'recipient' => ['name' => 'Test'],
        'parcels' => [],
        'print_options' => [
            'printOption' => [
                [
                    'outputFormat' => 'ZPL',
                    'paperFormat' => 'A7',
                ],
            ],
        ],
    ];

    $mockResponse = (object) [
        'orderResult' => (object) [
            'shipmentResponses' => (object) [
                'parcelInformation' => (object) [
                    'parcelLabelNumber' => '123456789',
                ],
            ],
            'output' => (object) [
                'content' => 'label',
                'format' => 'ZPL',
            ],
        ],
    ];

    $this->mockClient->shouldReceive('storeOrders')
        ->once()
        ->with(
            Mockery::on(function ($arg) {
                return $arg['printOption'][0]['outputFormat'] === 'ZPL'
                    && $arg['printOption'][0]['paperFormat'] === 'A7';
            }),
            Mockery::any()
        )
        ->andReturn($mockResponse);

    $this->service->createShipment($shipmentData);
});
