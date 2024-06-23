
# dbHandler Class

This class performs the connection with the DB via PDO and executes the SELECT/INSERT/UPDATE/DELETE queries and transactions.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Constructor](#constructor)
- [Destructor](#destructor)
- [getQuery](#getquery)
- [execQuery](#execquery)
- [Transactions](#transactions)
- [logQuery](#logquery)
- [References](#references)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/your-repository.git
   ```

2. Navigate to the project directory:
   ```bash
   cd your-repository
   ```

3. Ensure you have PHP installed and configured properly.

## Configuration

Create a `config.php` file in the project root with your database connection parameters and other settings:

```php
<?php
// config.php

return [
    'database' => [
        'servername' => 'your_db_server',
        'dbname' => 'your_db_name',
        'username' => 'your_db_username',
        'password' => 'your_db_password'
    ],
    'debugQuery' => true, // Set to true to enable query debugging
];
?>
```

## Usage

Include the `dbHandler` class in your project and instantiate it as needed.

### Constructor

The constructor establishes a connection to the database using the parameters specified in `config.php`.

```php
require 'path/to/dbHandler.php';

$db = new dbHandler();
```

### Destructor

The destructor closes the database connection at the end of the script.

```php
unset($db); // This will invoke the __destruct method
```

### getQuery

Executes a SQL SELECT statement and returns the results.

#### Parameters
- `string $query`: The SQL query to be executed.
- `array|null $data`: The data to be bound to the query parameters (default: null).
- `bool $fetchOne`: Determines whether to fetch one row (true) or all rows (false) (default: false).

#### Returns
- `array`: The fetched results.

#### Example

```php
$query = "SELECT * FROM users WHERE id = :id";
$data = [':id' => 1];
$result = $db->getQuery($query, $data, true);
print_r($result);
```

### execQuery

Executes a SQL INSERT, UPDATE, or DELETE statement and returns the result.

#### Parameters
- `string $query`: The SQL query to be executed.
- `array|null $data`: The data to be bound to the query parameters (default: null).

#### Returns
- `number|bool`: The execution result (last inserted ID for INSERT, true for successful UPDATE/DELETE, false otherwise).

#### Example

```php
$query = "INSERT INTO users (name, email) VALUES (:name, :email)";
$data = [':name' => 'John Doe', ':email' => 'john.doe@example.com'];
$insertId = $db->execQuery($query, $data);
echo $insertId;
```

### Transactions

Handle transactions composed of multiple queries.

#### beginTransaction

Starts a transaction.

```php
$db->beginTransaction();
```

#### commit

Commits the transaction.

```php
$db->commit();
```

#### inTransaction

Checks if a transaction is still active.

#### Returns
- `bool`: True if a transaction is active, false otherwise.

```php
if ($db->inTransaction()) {
    echo "Transaction is active";
}
```

#### rollBack

Rolls back the transaction.

```php
$db->rollBack();
```

### logQuery

Logs a query with bound parameters for debugging purposes.

#### Parameters
- `string $query`: The SQL query to be logged.
- `array|null $data`: The data to be bound to the query parameters (default: null).

#### Returns
- `string|null`: The logged query or null if debugging is disabled.

#### Example

```php
$query = "SELECT * FROM users WHERE id = :id";
$data = [':id' => 1];
$loggedQuery = $db->logQuery($query, $data);
echo $loggedQuery;
```

## References

- [PDO Tutorial](https://phpdelusions.net/pdo)
- [PHP PDO Prepared Statements to Prevent SQL Injection](https://websitebeaver.com/php-pdo-prepared-statements-to-prevent-sql-injection)
- [Stack Overflow: Are PDO prepared statements sufficient to prevent SQL injection?](https://stackoverflow.com/questions/134099/are-pdo-prepared-statements-sufficient-to-prevent-sql-injection?rq=1)
- [PDO - PHP: The Right Way](https://guidaphp.it/base/database/pdo)
