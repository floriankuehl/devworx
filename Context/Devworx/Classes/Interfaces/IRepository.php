<?php

namespace Devworx\Interfaces;

interface IRepository {
	public function getTable(): string;
	public function getPK(): string;
	
	public function getDetails(string $field=''): string|array;
	public function getFields(): array;
	public function getTypes(): array;
	public function getPlaceholders(): array;
	
	public function getConditions(): array;
	public function setConditions(array $conditions): void;
	
	public function getMapResult(): string;
	public function setMapResult(string $className): void;
	
	public function fromArray(array $data): bool;
	public function toArray(): array;
	
	public function error(): string;
	public function hasPK(): bool;
	
	public function readPK(): string;
	public function explain(): array;
	public function count(): int;
	public function countBy(string $field,mixed $value): int;
	
	public function findAll(string $fields='*',string $order='',int $offset=0,int $limit=0): array;
	public function findBy(string $key,$value,string $fields='*',string $order='',int $offset=0,int $limit=0): array;
	public function findOneBy(string $key,$value,string $fields='*'): array|object|null;
	public function filter(array $filter,string $fields='*',string $order='',int $offset=0,int $limit=0): array;
	
	public function findByUid($uid,string $fields='*'): array|object|bool;
	public function add(array $data): int;
	public function addAll(array $rows): array;
	
	public function put(array $data): bool;
	public function remove($uid): int;
	public function recycle($uid): int;
	public function delete($uid): int;
}