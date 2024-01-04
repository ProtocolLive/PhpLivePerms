<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

namespace ProtocolLive\PhpLivePerms;

/**
 * @version 2024.01.04.00
 */
final readonly class Perm{
  public function __construct(
    public bool $Read,
    public bool $Write,
    public bool $Owner
  ){}
}