<?php
// Protocol Corporation Ltda.
// https://github.com/ProtocolLive/PhpLive/
// Version 2021.04.25.00

class PhpLivePerms{
  private PhpLivePdo $PhpLivePdo;

  public function __construct(PhpLivePdo &$PhpLivePdo){
    $this->PhpLivePdo = $PhpLivePdo;
  }

  public function Access(string $Resource, int $User = null, string $Site = null):array{
    $return = ['r' => false, 'w' => false, 'o' => false];
    //Get resource id
    $result = $this->PhpLivePdo->BuildWhere([
      ['site', $Site, PdoStr],
      ['resource', $Resource, PdoStr]
    ]);
    $result = $this->PhpLivePdo->Run('
      select resource_id
      from sys_resources
      where ' . $result['Query']
    , $result['Tokens']);
    if(count($result) === 0):
      return $return;
    else:
      $Resource = $result[0][0];
    endif;
    // Permissions for everyone
    $result = $this->PhpLivePdo->Run('
      select r,w,o
      from sys_perms
      where resource_id=?
        and group_id=1
    ',[
      [1, $Resource, PdoInt]
    ]);
    if(count($result) === 1):
      $return = $result[0];
    endif;
    // Unauthenticated?
    if($User == 0):
      return $return;
    endif;
    // Admin?
    $result = $this->PhpLivePdo->Run("
      select *
      from sys_usergroup
      where group_id=3
        and user_id=?
    ",[
      [1, $User, PdoInt]
    ]);
    if(count($result) === 1):
      return ['r' => true, 'w' => true, 'o' => true];
    endif;
    // Others
    $result = $this->PhpLivePdo->Run("
      select r,w,o
      from sys_perms
      where resource_id=:resource
        and(
          user_id=:user
          or group_id=2
          or group_id in (select group_id from sys_groups where user_id=:user)
        )
      order by r,w,o
    ",[
      [':resource', $Resource, PdoInt],
      [':user', $User, PdoInt]
    ]);
    if(count($result) > 0):
      $return = $result[0];
    endif;
    return $return;
  }
}