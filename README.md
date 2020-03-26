# php7_api

A PHP basic starter for single url API development. It provides the basic functionalities with different authorization handles as well as Mysqli Wrapper.

## Requirements
  - PHP 7.2 or higher with mysqli extension

## Installation
    composer require alisajjad/php7_api

## File Structure

  - app: Contains core app files
  - components: Contains your custom controllers
  - connection: Mysqli Wrapper & result storage objects
  - includes: Contains common functions, traits, interfaces
  - tests: Test cases
  - configs.php: App configurations
  - routes.php: All app routes
  
## Configurations

All the configurations can be modified/added through configs.php

  - Database
      - host
      - user
      - password
      - db
      - port
      - socket
  - Environment
      - debug: true on production, false on development
      - url: url of production/development server
  - Authorization
      - None: No Authorization
      - Basic: Authorization with username & password
      - Bearer: Authorization with token
      - Digest: Authorization with encrypted username & password
      - Header: Custom header authorization    

## Usage
    
After installing add your routes in `routes.php`

    [
        'welcome' => [
            'class' => \PHP7API\Components\Welcome::class,
            'method' => 'welcome'
        ],
        'welcome_args' => [
            'class' => \PHP7API\Components\Welcome::class,
            'method' => 'welcomeArgs',
            'requires' => [
                'param_1',
                'param_2',
            ]
        ],
    ]

Where welcome is route name and class, method is the what the app will call. The requires makes the given parameters required in current route inputs.

A Basic Component:

    namespace PHP7API\Components;
    
    
    class Welcome extends Component{
    
        public function welcome($request, $route){
    
            return [
                'success' => 1,
                'message' => 'success'
            ];
        }
    }

Which outputs the response:

    {"success":1,"message":"success","time":"2020-03-26 21:23:18"}
    
The "`$request`" parameter contains all the given input 

The "`$route`" parameter contains current Route information.

### Call Api

Using Curl

    $ch = curl_init();
    
    $curlData = http_build_query([
        'route' => 'welcome_args',
        'param_1' => 'value_1',
        'param_2' => 'value_2',
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer your_token'
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close ($ch);
    
    $response = json_decode($server_output, true);
    
### Mysqli Usage

Initiate the object:

    $db = MySql::instance();
or 
    
    $db = MySql::init();
    
`MySql::instance()` uses the db configurations from `configs.php`

`MySql::init()` uses the given configurations from arguments or `configs.php` if no argument is given.

Execute Query:

    $result = $db->exec($sql);

`$result` is true on success, false on query failure.

Execute Fetch Query:

    $result = $db->fetch($sql);

`$result` is MysqlResultCollection object on success, null on query failure.

#### MysqlResultCollection

All the query results will be stored in this object.

Common Methods:
  - getAll() - returns all rows in array
  - getRowsNum() - returns rows count
  - isEmpty() - checks if result is empty
  - first() - Returns first row
  - last() - Returns last row
  - nth() - Returns nth index of row
  
The first row can be directly accessed simply by:

    $result->first_row_column;
or

    $result->first()->first_row_column;
    
Using Loop:

    foreach ($user as $name => $value):
        echo $value->column;
    endforeach;


## Limitation

  - This app only uses one url for all the routes
  - Only has MySQLi Wrapper yet.
  - Doesn't support OAuth1 & OAuth2 yet.
