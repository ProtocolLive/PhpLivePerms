<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive

/**
 * @version 2023.10.27.00
 */
final class PhpLivePerms{
  public function __construct(
    private PhpLiveDb $PhpLiveDb
  ){}

  public function __invoke(string $Resource, int $User = null):PhpLivePermsAccess{
    //Get resource id
    $consult = $this->PhpLiveDb->Select('sys_resources');
    $consult->WhereAdd('resource', $Resource, PhpLiveDbTypes::Str);
    $result = $consult->Run();
    if(count($result) === 0):
      return new PhpLivePermsAccess(true, false, false, false);
    else:
      $Resource = $result[0]['resource_id'];
    endif;
    // Permissions for everyone
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->WhereAdd('resource_id', $Resource, PhpLiveDbTypes::Int);
    $result->WhereAdd('group_id', 1, PhpLiveDbTypes::Int);
    $result = $result->Run();
    if(count($result) === 1):
      return new PhpLivePermsAccess(
        $result[0]['r'],
        $result[0]['w'],
        false
      );
    endif;
    // Unauthenticated?
    if($User == 0):
      return new PhpLivePermsAccess(true, false, false, false);
    endif;
    // Admin?
    $result = $this->PhpLiveDb->Select('sys_usergroup');
    $result->WhereAdd('user_id', $User, PhpLiveDbTypes::Int);
    $result->WhereAdd('group_id', 3, PhpLiveDbTypes::Int);
    $result = $result->Run();
    if(count($result) === 1):
      return new PhpLivePermsAccess(true, true, false);
    endif;
    // Others
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->WhereAdd('resource_id', $Resource, PhpLiveDbTypes::Int);
    $result->WhereAdd(
      'user_id',
      $User,
      PhpLiveDbTypes::Int,
      Parenthesis: PhpLiveDbParenthesis::Open
    );
    $result->WhereAdd(
      'group_id',
      'select group_id from sys_usergroup where user_id=:user_id',
      PhpLiveDbTypes::Sql,
      PhpLiveDbOperators::In,
      PhpLiveDbAndOr::Or,
      CustomPlaceholder: 'group1'
    );
    $result->WhereAdd(
      'group_id',
      1,
      PhpLiveDbTypes::Int,
      AndOr: PhpLiveDbAndOr::Or,
      CustomPlaceholder: 'group2'
    );
    $result->WhereAdd(
      'group_id',
      2,
      PhpLiveDbTypes::Int,
      AndOr: PhpLiveDbAndOr::Or,
      Parenthesis: PhpLiveDbParenthesis::Close,
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
    return new PhpLivePermsAccess(
      $return['r'],
      $return['w'],
      $return['o'],
    );
  }
}