# PHP-SSLLabs-API
This PHP library provides basic access to the SSL Labs API.

It's build upon the official API documentation at https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md
```PHP
<?php

require_once 'sslLabsApi.php';

//Return API response as JSON string
$api = new sslLabsApi();

//Return API response as JSON object
//$api = new sslLabsApi(true);

//Set content-type header for JSON output
header('Content-Type: application/json');

//get API information
var_dump($api->fetchApiInfo());

?>
```
## Methods
### fetchApiInfo()
No parameters needed

Returns an Info object (see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#info).

### fetchStatusCodes()
No parameters needed

Returns a StatusCodes instance (see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#statuscodes).

### fetchHostInformation()
See https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#invoke-assessment-and-check-progress for parameter description.

| Parameter           | Type    | Default value |          |
|---------------------|---------|---------------|----------|
| **host**           | string  |               | Required |
| **publish**        | boolean | false         |          |
| **startNew**       | boolean | false         |          |
| **fromCache**      | boolean | false         |          |
| **maxAge**         | int     | null          |          |
| **all**            | string  | null          |          |
| **ignoreMismatch** | boolean | false         |          |

Returns a Host object (see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#host).

Make sure to check the 'status' attribute inside Host object.

### fetchHostInformationCached()
You can also use fetchHostInformation() with the proper parameters, this is just a helper function.

| Parameter           | Type    | Default value |          |
|---------------------|---------|---------------|----------|
| **host**           | string  |               | Required |
| **maxAge**         | int     | null          |          |
| **publish**        | boolean | false         |          |
| **ignoreMismatch** | boolean | false         |          |

Returns a Host object (see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#host).

Also make sure to check the 'status' attribute inside Host object.

### fetchEndpointData()
See https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#retrieve-detailed-endpoint-information for parameter description.

| Parameter      | Type    | Default value |          |
|----------------|---------|---------------|----------|
| **host**      | string  |               | Required |
| **s**         | string  |               | Required |
| **fromCache** | boolean | false         |          |

Returns an Endpoint object (see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md#endpoint).

### Custom API calls
Use sendApiRequest() method to create custom API calls.

| Parameter       | Type   | Default value |          |
|-----------------|--------|---------------|----------|
| **apiCall**    | string |               | Required |
| **parameters** | array  |               |          |

```PHP
$api->sendApiRequest('apiCallName', array('p1' => 'p1_value', 'p2' => 'p2_value'));
```

### getReturnJsonObjects()
Getter for returnJsonObjects

### setReturnJsonObjects()
Setter for returnJsonObjects

| Parameter             | Type    | Default value |          |
|-----------------------|---------|---------------|----------|
| **returnJsonObjects** | boolean |               | Required |

## Example output (as JSON strings)
### Get API information
```PHP
$api->fetchApiInfo();
```
```JSON
{
    "engineVersion": "1.15.1",
    "criteriaVersion": "2009i",
    "clientMaxAssessments": 25,
    "maxAssessments": 25,
    "currentAssessments": 0,
    "messages": [
        "This assessment service is provided free of charge by Qualys SSL Labs, subject to our terms and conditions: https://www.ssllabs.com/about/terms.html"
    ]
}
```

### Get host information
```PHP
$api->fetchHostInformation('https://www.google.de');
```
```JSON
{
    "host": "https://www.google.de",
    "port": 443,
    "protocol": "HTTP",
    "isPublic": false,
    "status": "READY",
    "startTime": 1427195976527,
    "testTime": 1427196284525,
    "engineVersion": "1.15.1",
    "criteriaVersion": "2009i",
    "endpoints": [
        {
            "ipAddress": "74.125.239.119",
            "serverName": "nuq05s01-in-f23.1e100.net",
            "statusMessage": "Ready",
            "grade": "B",
            "hasWarnings": false,
            "isExceptional": false,
            "progress": 100,
            "duration": 77376,
            "eta": 1610,
            "delegation": 3
        },
        {
            "ipAddress": "74.125.239.120",
            "serverName": "nuq05s01-in-f24.1e100.net",
            "statusMessage": "Ready",
            "grade": "B",
            "hasWarnings": false,
            "isExceptional": false,
            "progress": 100,
            "duration": 76386,
            "eta": 1609,
            "delegation": 3
        },
        {
            "ipAddress": "74.125.239.127",
            "serverName": "nuq05s01-in-f31.1e100.net",
            "statusMessage": "Ready",
            "grade": "B",
            "hasWarnings": false,
            "isExceptional": false,
            "progress": 100,
            "duration": 76937,
            "eta": 1608,
            "delegation": 3
        },
        {
            "ipAddress": "74.125.239.111",
            "serverName": "nuq05s01-in-f15.1e100.net",
            "statusMessage": "Ready",
            "grade": "B",
            "hasWarnings": false,
            "isExceptional": false,
            "progress": 100,
            "duration": 77171,
            "eta": 1606,
            "delegation": 3
        }
    ]
}
```

### Get endpoint information
```PHP
$api->fetchEndpointData('https://www.google.de', '74.125.239.111');
```

(just an except of the entire JSON output)
```JSON
{
    "ipAddress": "74.125.239.111",
    "serverName": "nuq05s01-in-f15.1e100.net",
    "statusMessage": "Ready",
    "grade": "B",
    "hasWarnings": false,
    "isExceptional": false,
    "progress": 100,
    "duration": 77171,
    "eta": 1609,
    "delegation": 3,
    "details": {
        "hostStartTime": 1427195976527,
        "key": {},
        "cert": {},
        "chain": {},
        "protocols": [],
        "suites": {},
        "serverSignature": "gws",
        "prefixDelegation": true,
        "nonPrefixDelegation": true,
        "vulnBeast": false,
        "renegSupport": 2,
        "sessionResumption": 1,
        "compressionMethods": 0,
        "supportsNpn": true,
        "npnProtocols": "h2-15 h2-14 spdy/3.1 spdy/3 http/1.1",
        "sessionTickets": 1,
        "ocspStapling": false,
        "sniRequired": false,
        "httpStatusCode": 200,
        "supportsRc4": true,
        "forwardSecrecy": 2,
        "rc4WithModern": true,
        "sims": {},
        "heartbleed": false,
        "heartbeat": false,
        "openSslCcs": 1,
        "poodleTls": 1,
        "fallbackScsv": true
    }
}
```

# Terms and Conditions
As this is just a PHP library for SSL Labs API please refer to SSL Labs terms and conditions at https://www.ssllabs.com/about/terms.html
