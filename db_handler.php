<?php

/**
 * Class dbHandler
 *
 * This class performs the connection with the DB via PDO and executes the SELECT/INSERT/UPDATE/DELETE queries and transactions.
 *
 * References:
 * https://guidaphp.it/base/database/pdo
 * https://phpdelusions.net/pdo
 * https://websitebeaver.com/php-pdo-prepared-statements-to-prevent-sql-injection
 * https://stackoverflow.com/questions/134099/are-pdo-prepared-statements-sufficient-to-prevent-sql-injection?rq=1
 */
class dbHandler {

  private $conn;
  private $config;



  /**
   * Function __construct
   * Get a connection to the DB using PDO and the parameters loaded from the config file.
   */
  public function __construct() {

    $this->config = include('config.php'); // Load configuration

    if ($this->conn == NULL) { // Check if the connection is already established
      // Load the connection parameters
      $servername = $this->config['database']['servername'];
      $dbname = $this->config['database']['dbname'];
      $username = $this->config['database']['username'];
      $password = $this->config['database']['password'];

      // Set the PDO options
      $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Error mode to exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch mode to associative arrays
        PDO::ATTR_EMULATE_PREPARES => false, // Disable emulated prepared statements to improve security against SQL injection
        PDO::MYSQL_ATTR_FOUND_ROWS => true // Without this, zero value will be returned when updating a table with the same values
      );

      // Create the connection
      try {
        $this->conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password, $options);
      } catch (PDOException $e) {
        throw new Exception('Connection failed: ' . $e->getMessage()); // Do not get the full $e contents in order to not show dangerous informations
      }
    }
    
  }



  /**
   * Function __destruct
   * Closes the database connection at the end of the script.
   */
  public function __destruct() {
    $this->conn = null; // Close the PDO connection
  }



  /**
   * Function processElement
   * Cleans a data element.
   *
   * @param mixed $element The data element to be cleaned.
   * @return mixed The cleaned data element.
   */
  private function processElement($element) {
    $unquotedVar = str_replace('`', '``', $element); // Remove any backticks quotation
    $trimmedVar = trim($unquotedVar); // Trim any space before and after the string
    return $trimmedVar;
  }



  /**
   * Function getQuery
   * This function prepares and executes a SQL SELECT statement and returns the results.
   *
   * @param string $query The SQL query to be executed.
   * @param array|null $data The data to be bound to the query parameters.
   * @param bool $fetchOne Determines whether to fetch one row (true) or all rows (false).
   * @return array The fetched results.
   */
  public function getQuery($query, $data = null, $fetchOne = false) {

    // Process the data, if provided, in order to clean it
    if ($data !== null) {
      $cleanedData = array_map(array($this, 'processElement'), $data); // Use the processElement method of this class
    } else {
      $cleanedData = null;
    }

    // Prepare and execute the query with the cleaned data
    $stmt = $this->conn->prepare($query);
    $stmt->execute($cleanedData);

    // Fetch the results depending on the $fetchOne parameter
    if ($fetchOne) {
      $result = $stmt->fetch(); // If $fetchOne is true, fetch a single row
    } else {
      $result = $stmt->fetchAll(); // If $fetchOne is false (or not provided), fetch all rows
    }

    // If there are no results, return an empty array instead of false
    if ($result === false) {
      $result = [];
    }

    $stmt = null; // Close the statement

    // Detect and convert the encoding of the result to UTF-8
    array_walk_recursive($result, function (&$item, $key) {
      if (is_string($item)) {
        $encoding = mb_detect_encoding($item, mb_detect_order(), true);
        if ($encoding && $encoding != 'UTF-8') {
          $item = mb_convert_encoding($item, 'UTF-8', $encoding);
        }
      }
    });

    return $result; // Return the result

  }



  /**
   * Function execQuery
   * This function prepares and executes a SQL INSERT, UPDATE, and DELETE statement and returns the result.
   *
   * @param string $query The SQL query to be executed.
   * @param array|null $data The data to be bound to the query parameters.
   * @return number|bool The execution result.
   */
  public function execQuery($query, $data = null) {

    if ($data !== null) {
      $cleanedData = array_map(function ($element) {
        if ($element === null) {
          return null;
        }
        return $this->processElement($element);
      }, $data);
    } else {
      $cleanedData = null;
    }

    // Prepare and execute the query with the cleaned data
    $stmt = $this->conn->prepare($query);
    $stmt->execute($cleanedData);

    // Prepare the results to be returned
    if (strpos(strtolower($query), 'insert') !== false) {
      $result = $this->conn->lastInsertId(); // If the query is an INSERT statement, return the last inserted ID
    } elseif ($stmt->rowCount() > 0) {
      $result = true; // If the query is an UPDATE or a DELETE statement and some rows are affected, return true
    } else {
      $result = false; // If the query is an UPDATE or a DELETE statement did not affect any row, return false
    }

    $stmt = null; // Close the statement

    return $result; // Return the result

  }



  /**
   * Functions for transactions handling
   * These functions handle transactions composed of multiple queries.
   */
  public function beginTransaction() { // Start the transaction block
    $this->conn->beginTransaction();
  }

  public function commit() { // Commit the transaction
    $this->conn->commit();
  }

  public function inTransaction() { // Check if a transaction is still active
    return $this->conn->inTransaction();
  }

  public function rollBack() { // Roll back the transaction
    $this->conn->rollBack();
  }



  /**
   * Function logQuery
   * Function to log a query with bound parameters for debugging purposes.
   *
   * @param string $query The SQL query to be logged.
   * @param array|null $data The data to be bound to the query parameters.
   */
  public function logQuery($query, $data = null) {

    $debugQuery = $this->config['debugQuery'];

    if ($debugQuery) { // Check if the query debugging is enabled
      if ($data !== null) {
        foreach ($data as $key => $value) {
          $safeValue = $this->conn->quote($value); // Convert the value to a safely quoted string
          $query = str_replace($key, $safeValue, $query); // Replace the placeholder with the quoted value
        }
      }
    } else {
      $query = null;
    }

    return $query; // Return the query

  }



}