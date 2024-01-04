<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

namespace ProtocolLive\PhpLivePerms;
use ProtocolLive\PhpLiveDb\{
  AndOr,
  Operators,
  Parenthesis,
  PhpLiveDb,
  Types
};

/**
 * @version 2024.01.04.01
 */
final class Perms{
  public function __construct(
    private PhpLiveDb $PhpLiveDb
  ){}

  public function Get(
    string $Resource,
    int $User = null
  ):Perm{
    //Get resource id
    $result = $this->PhpLiveDb->Select('sys_resources')
    ->WhereAdd('resource', $Resource, Types::Str)
    ->Run();
    if(count($result) === 0):
      return new Perm(true, false, false);
    else:
      $Resource = $result[0]['resource_id'];
    endif;
    // Permissions for everyone
    $result = $this->PhpLiveDb->Select('sys_perms')
    ->WhereAdd('resource_id', $Resource, Types::Int)
    ->WhereAdd('group_id', 1, Types::Int)
    ->Run();
    if(count($result) === 1):
      return new Perm(
        $result[0]['r'],
        $result[0]['w'],
        false
      );
    endif;
    // Unauthenticated?
    if($User === 0):
      return new Perm(false, false, false);
    endif;
    // Admin?
    $result = $this->PhpLiveDb->Select('sys_usergroup')
    ->WhereAdd('user_id', $User, Types::Int)
    ->WhereAdd('group_id', 3, Types::Int)
    ->Run();
    if(count($result) === 1):
      return new Perm(true, true, false);
    endif;
    // Others
    $result = $this->PhpLiveDb->Select('sys_perms')
    ->WhereAdd('resource_id', $Resource, Types::Int)
    ->WhereAdd(
      'user_id',
      $User,
      Types::Int,
      Parenthesis: Parenthesis::Open
    )
    ->WhereAdd(
      'group_id',
      'select group_id from sys_usergroup where user_id=:user_id',
      Types::Sql,
      Operators::In,
      AndOr::Or,
      CustomPlaceholder: 'group1'
    )
    ->WhereAdd(
      'group_id',
      1,
      Types::Int,
      AndOr: AndOr::Or,
      CustomPlaceholder: 'group2'
    )
    ->WhereAdd(
      'group_id',
      2,
      Types::Int,
      AndOr: AndOr::Or,
      Parenthesis: Parenthesis::Close,
      CustomPlaceholder: 'group3'
    )
    ->Order('allow desc')
    ->Run();
    $return = ['r' => false, 'w' => false, 'o' => false];
    foreach($result as $line):
      if($line['allow']):
        if($line['r'] == 1):
          $return['r'] = true;
        elseif($line['w'] == 1):
          $return['w'] = true;
        elseif($line['o'] == 1):
          $return['o'] = true;
        endif;
      else:
        if($line['r'] == 1):
          $return['r'] = false;
        elseif($line['w'] == 1):
          $return['w'] = false;
        elseif($line['o'] == 1):
          $return['o'] = false;
        endif;
      endif;
    endforeach;
    return new Perm(
      $return['r'],
      $return['w'],
      $return['o'],
    );
  }
}