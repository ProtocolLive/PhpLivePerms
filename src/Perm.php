<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

namespace ProtocolLive\PhpLivePerms;

/**
 * @version 2023.10.27.02
 */
final class Perm{
  public function __construct(
    private PhpLiveDb $PhpLiveDb
  ){}

  public function __invoke(string $Resource, int $User = null):Perms{
    //Get resource id
    $consult = $this->PhpLiveDb->Select('sys_resources');
    $consult->WhereAdd('resource', $Resource, Types::Str);
    $result = $consult->Run();
    if(count($result) === 0):
      return new Perms(true, false, false, false);
    else:
      $Resource = $result[0]['resource_id'];
    endif;
    // Permissions for everyone
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->WhereAdd('resource_id', $Resource, Types::Int);
    $result->WhereAdd('group_id', 1, Types::Int);
    $result = $result->Run();
    if(count($result) === 1):
      return new Perms(
        $result[0]['r'],
        $result[0]['w'],
        false
      );
    endif;
    // Unauthenticated?
    if($User == 0):
      return new Perms(true, false, false, false);
    endif;
    // Admin?
    $result = $this->PhpLiveDb->Select('sys_usergroup');
    $result->WhereAdd('user_id', $User, Types::Int);
    $result->WhereAdd('group_id', 3, Types::Int);
    $result = $result->Run();
    if(count($result) === 1):
      return new Perms(true, true, false);
    endif;
    // Others
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->WhereAdd('resource_id', $Resource, Types::Int);
    $result->WhereAdd(
      'user_id',
      $User,
      Types::Int,
      Parenthesis: Parenthesis::Open
    );
    $result->WhereAdd(
      'group_id',
      'select group_id from sys_usergroup where user_id=:user_id',
      Types::Sql,
      Operators::In,
      AndOr::Or,
      CustomPlaceholder: 'group1'
    );
    $result->WhereAdd(
      'group_id',
      1,
      Types::Int,
      AndOr: AndOr::Or,
      CustomPlaceholder: 'group2'
    );
    $result->WhereAdd(
      'group_id',
      2,
      Types::Int,
      AndOr: AndOr::Or,
      Parenthesis: Parenthesis::Close,
      CustomPlaceholder: 'group3'
    );
    $result->Order('allow desc');
    $result->Run(Fetch: true);
    $return = ['r' => false, 'w' => false, 'o' => false];
    while(($line = $result->Fetch()) !== false):
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
    endwhile;
    return new Perms(
      $return['r'],
      $return['w'],
      $return['o'],
    );
  }
}