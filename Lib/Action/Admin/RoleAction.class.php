<?php
/**
 * 角色操作表
 */
class RoleAction extends CommonAction {
  public function index() {
    $this->assign("roles", D("Role")->roleList());
    $this->display();
  }

  public function add() {
    if(IS_POST) {
      $this->checkToken();
      if(D("Role")->addRole() > 0) {
        $this->success('操作成功！',__GROUP__.'/Role/index');
      } else {
        $this->error('操作失败！',__GROUP__.'/Role/index');
      }
    } else {
      $this->display();
    }
  }

  public function edit() {
    $nid = $_REQUEST['nid'];
    if(IS_POST) {
      $this->checkToken();
      if(D("Role")->editRole($nid) > 0) {
        $this->success('操作成功！',__GROUP__.'/Role/index');
      } else {
        $this->error('操作失败！',__GROUP__.'/Role/index');
      }
    } else {
      if(empty($nid)) {
        $this->error('异常操作！',__GROUP__.'/Role/index');
      }
      $this->assign('nid',$nid);
      $this->assign("role", D("Role")->getRole($nid));
      $this->display();
    }
  }

  public function del() {
    $nid = $_GET['nid'];
    if(empty($nid)) {
      $this->error('异常操作！',__GROUP__.'/Role/index');
    }
    $m = M('role_user');
    $count = $m->where("user_id = {$nid}")->count();
    if($count > 0) {
      $this->error('请先删除该角色下的管理员帐号！',__GROUP__.'/Role/index');
    }
    if(D("Role")->delRole($nid) > 0) {
      $this->success('操作成功！',__GROUP__.'/Role/index');
    } else {
      $this->error('操作失败！',__GROUP__.'/Role/index');
    }
  }

  public function limits() {
    $role_id = intval($_REQUEST['role_id']);
    if(IS_POST) {
      $mod = D('Access');
      $site = $_POST['site'];
      if (!empty($_POST['menuid'])) {
        $sql = "INSERT INTO ". C("DB_PREFIX") ."access (`role_id`,`node_id`,`siteid`) VALUES ";
        foreach ($_POST['menuid'] as $key => $value) {
          $value = intval($value);
          $sql .= "( {$role_id}, {$value}, {$site} ),";
        }
        $sql = substr($sql, 0, strlen($sql) -1 ) . ';';
        $mod->where(array( 'role_id' => $role_id, 'siteid' => $site ))->delete();
        $rs = $mod->query($sql);
        if ( $rs === false ) {
          $this->error("操作失败");
        } else {
          $this->success('操作成功！', 'index');
        }
      } else {
        $mod->where(array( 'role_id' => $role_id, 'siteid' => $site ))->delete();
        $this->success('操作成功！', 'index');
      }
    } else {
      $menus = D("Menu")->nodeList();
      $authorized = array();
      $sites = D('Site')->select();
      if ( isset($_GET['siteid']) ) {
        $current_site = D('Site')->find( $_GET['siteid'] );
      }
      if ( !isset( $current_site ) || empty($current_site) ) {
        $current_site = current($sites);
      }

      $access_list = D("Access")->where( array( 'role_id' => $role_id, 'siteid' => $current_site['id'] ) )->field("node_id")->select();

      foreach ($access_list as $key => $value) {
        $authorized[$value['node_id']] = true;
      }
      $this->assign('current_site',$current_site);
      $this->assign('sites', $sites);
      $this->assign('authorized',$authorized);
      $this->assign("menus", $menus);
      $this->assign("role_id", $role_id);
      $this->display();
    }
  }
}
?>