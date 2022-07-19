<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive
//Version 2022.06.28.00

class PhpLivePerms{
  private PhpLiveDb $PhpLiveDb;

  public function __construct(PhpLiveDb &$PhpLiveDb){
    $this->PhpLiveDb = $PhpLiveDb;
  }

  public function Access(string $Resource, int $User = null):array{
    $return = ['r' => false, 'w' => false, 'o' => false];
    //Get resource id
    $consult = $this->PhpLiveDb->Select('sys_resources');
    $consult->Fields('resource_id');
    $consult->WhereAdd('resource', $Resource, PhpLiveDbTypes::Str);
    $result = $consult->Run();
    if(count($result) === 0):
      return $return;
    else:
      $Resource = $result[0]['resource_id'];
    endif;
    // Permissions for everyone
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->Fields('r,w,o');
    $result->WhereAdd('resource_id', $Resource, PhpLiveDbTypes::Int);
    $result->WhereAdd('group_id', 1, PhpLiveDbTypes::Int);
    $result = $result->Run();
    if(count($result) === 1):
      $return = $result[0];
    endif;
    // Unauthenticated?
    if($User == 0):
      return $return;
    endif;
    // Admin?
    $result = $this->PhpLiveDb->Select('sys_usergroup');
    $result->WhereAdd('user_id', $User, PhpLiveDbTypes::Int);
    $result->WhereAdd('group_id', 3, PhpLiveDbTypes::Int);
    $result = $result->Run();
    if(count($result) === 1):
      return ['r' => true, 'w' => true, 'o' => false];
    endif;
    // Others
    $result = $this->PhpLiveDb->Select('sys_perms');
    $result->Fields('r,w,o');
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
    $result = $result->Run();
    if(count($result) > 0):
      $return = $result[0];
    endif;
    return $return;
  }
}