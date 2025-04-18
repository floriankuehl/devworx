<?php

namespace Devworx;

/**
 * An interface for databases
 */
interface IDatabase {
  function conditions(): array;
  
  function connect(): bool;
  function disconnect(): bool;
  function connected(): bool;
  function error(): string;
  function statement(string $query,string $format,array $values);
  function prepare(string $query,string $format,array $values);
  function query(string $query,bool $one,int $mode);
  function insertID(): ?int;
  function escape(string $value): string;
}

?>