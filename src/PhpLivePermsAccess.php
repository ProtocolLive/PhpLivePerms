<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

namespace ProtocolLive\PhpLivePerms;

/**
 * @version 2023.10.27.01
 */
final class PhpLivePermsAccess{
  public function __construct(
    public readonly bool $Read,
    public readonly bool $Write,
    public readonly bool $Owner
  ){}
}