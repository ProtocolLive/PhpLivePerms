<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

/**
 * @version 2023.10.27.00
 */
final class PhpLivePermsAccess{
  public function __construct(
    public readonly bool $Read,
    public readonly bool $Write,
    public readonly bool $Owner
  ){}
}