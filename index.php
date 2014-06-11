<?php

require_once('vendor/autoload.php');
setlocale(LC_MONETARY, 'en_AU');
error_reporting(E_ALL^E_NOTICE);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

$cardData = array(
    'number' => '4200000000000000',
    'cvc' => 333,
    'expiry_month' => 6,
    'expiry_year' => 2016,
    'name' => 'Mr Matthew Setter',
    'scheme' => 'visa',
    'address_line1' => 'Fürther Straße 1',
    'address_line2' => null,
    'address_city' => 'Fürth',
    'address_postcode' => '64658',
    'address_state' => 'Krumbach',
    'address_country' => 'Germany',
);

/**
 * Setup a client with default auth credentials
 */
$client = new Client([
    'defaults' => [
        'auth' => ['']      // add your secret key here
    ]
]);

/**
 * Simple function to iterate over the charge information
 *
 * @param \GuzzleHttp\Message\Response $response
 * @return null
 */
function display_charges_list(GuzzleHttp\Message\Response $response)
{
    if (count($response->json()['response'])) {
        $charges = $response->json()['response'];
        print "Available charges\n";
        foreach ($charges as $charge) {
            printf(
                "token:  %s | transaction date: %s | currency:  %s | amount:  %s | fees: %s\n",
                $charge['token'], $charge['created_at'], $charge['currency'],
                money_format('%(#5n', $charge['amount']),
                money_format('%(#5n', $charge['total_fees'])
            );
        }
    } else {
        print "\nNo charges available in response object\n";
    }
}

/**
 * Get all the charges on the account
 */
display_charges_list($client->get('https://test-api.pin.net.au/1/charges', [
    'query' => [
        'page' => 1
    ]
]));

/**
 * Search the account for charges within a date range
 */
display_charges_list($client->get('https://test-api.pin.net.au/1/charges/search', [
    'query' => [
        'start_date' => '2014/06/10',
        'sort' => 'created_at',
        'direction' => 1
    ]
]));

/**
 * Check if a charge succeeded
 */
print_r(
    $client->get('https://test-api.pin.net.au/1/charges/ch_1AH2MbKXxEdHxT_WR37NHg')
           ->json()['response']['success']
);

/**
 * Create a customer
 */
try {
    $response = $client->post('https://test-api.pin.net.au/1/customers', [
        'body' => [
            'email' => 'john@anywhere.net',
            'card' => array(
                'number' => '4200000000000000',
                'cvc' => 333,
                'expiry_month' => 6,
                'expiry_year' => 2016,
                'name' => 'Mr John Citizen',
                'scheme' => 'visa',
                'address_line1' => 'Fürther Straße 123',
                'address_line2' => null,
                'address_city' => 'Fürth',
                'address_postcode' => '90000',
                'address_state' => 'Krumbach',
                'address_country' => 'Germany',
            )
        ]
    ]);
} catch (ClientException $e) {
    var_dump($e->getResponse()->json());
}

/**
 * Update an existing customer's details
 */
try {
    $response = $client->put('https://test-api.pin.net.au/1/customers/cus_w-PgU5QOqUDValSBVKhwFA', [
        'body' => [
            'email' => 'matthew@maltblue.com',
            'card' => array(
                'number' => '4200000000000000',
                'cvc' => 333,
                'expiry_month' => 6,
                'expiry_year' => 2016,
                'name' => 'Mr Matthew Setter',
                'scheme' => 'visa',
                'address_line1' => 'Fürther Straße 1',
                'address_line2' => null,
                'address_city' => 'Fürth',
                'address_postcode' => '64658',
                'address_state' => 'Krumbach',
                'address_country' => 'Germany',
            )
        ]
    ]);
    print "Customer successfully updated";
} catch (ClientException $e) {
    var_dump($e->getResponse()->json());
}

/**
 * Add a charge for a existing customer, using their customer token to link it to them.
 */
try {
    $response = $client->post('https://test-api.pin.net.au/1/charges', [
        'body' => [
            'email' => 'matthew@maltblue.com',
            'description' => 'Sennheiser MM 550-X TRAVEL Headphones',
            'amount' => 349,
            'ip_address' => '127.0.0.1',
            'currency' => 'EUR',
            'customer_token' => 'cus_w-PgU5QOqUDValSBVKhwFA'
        ]
    ]);
    print "Customer successfully charged";
} catch (ClientException $e) {
    var_dump($e->getResponse()->json());
}

/**
 * Add a refund for the previous charge created
 */
try {
    $response = $client->post('https://test-api.pin.net.au/1/charges/ch_fKwgwYWIYAvkYCaik2NNsw/refunds', [
        'body' => [
            'amount' => 349,
        ]
    ]);
    print "Charge successfully refunded";
    var_dump($response);
} catch (ClientException $e) {
    var_dump($e->getResponse()->json());
}

/**
 * Add another charge for the customer
 */
try {
    $response = $client->post('https://test-api.pin.net.au/1/charges', [
        'body' => [
            'email' => 'matthew@maltblue.com',
            'description' => 'Sennheiser MM 550-X TRAVEL Headphones',
            'amount' => 34900,
            'ip_address' => '127.0.0.1',
            'currency' => 'EUR',
            'customer_token' => 'cus_w-PgU5QOqUDValSBVKhwFA'
        ]
    ]);
    print "Charge successfully created";
} catch (ClientException $e) {
    var_dump($e->getResponse()->json());
}