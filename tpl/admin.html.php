<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo $this->title; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<p><a href="?i=home">Home</a> | <a href="?i=logout">Logout</a></p>
<h1>Administration</h1>
<p>
  <?php
    if(!empty($this->messages))
      print_r($this->messages)
  ?>
</p>
<form action="./?i=admin" method="POST">
  <ul style="list-style: none;">
    <li><label for="username">Username</label><input id="username" name="username" type="text"></li>
    <li><label for="password">Password</label><input id="password" name="password" type="password"></li>
    <li><label for="password_confirmation">Password Confirmation</label><input id="password_confirmation" name="password_confirmation" type="password"></li>
    <li><label for="role">Administrator ?</label><input id="role" name="role" type="checkbox" value="1" /></li>
    <li><input type="submit"></li>
  </ul>
</form>
</body>
</html>