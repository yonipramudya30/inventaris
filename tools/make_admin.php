<?php
require __DIR__.'/../config/db.php';
$username='admin';
$password='admin123';
$res=$conn->query("SELECT COUNT(*) c FROM users WHERE username='admin'");
$c=(int)$res->fetch_assoc()['c'];
if($c===0){
  $hash=password_hash($password,PASSWORD_DEFAULT);
  $stmt=$conn->prepare('INSERT INTO users(username,password) VALUES(?,?)');
  $stmt->bind_param('ss',$username,$hash);
  $stmt->execute();
  echo 'Admin dibuat. Username: admin, Password: admin123';
}else{
  echo 'Admin sudah ada';
}
