<?php 
	session_start();
	$pageTitle = 'Admin Login';

	if(isset($_SESSION['username_restaurant_qRewacvAqzA']) && isset($_SESSION['password_restaurant_qRewacvAqzA']))
	{
		header('Location: dashboard.php');
	}
?>



<?php include 'connect.php'; ?>
<?php include 'Includes/functions/functions.php'; ?>
<?php include 'Includes/templates/header.php'; ?>

	

	<div class="login">
			<form class="login-container validate-form" name="login-form" action="index.php" method="POST" onsubmit="return validateLoginForm()">
					<span class="login100-form-title p-b-32">
					Вход администратора
						</span>
						
						<?php

							

								if(isset($_POST['admin_login']))
								{
										$username = test_input($_POST['username']);
										$password = test_input($_POST['password']);
										$hashedPass = sha1($password);

									

										$stmt = $con->prepare("Select user_id, username, password from users where username = ? and password = ?");
										$stmt->execute(array($username,$hashedPass));
										$row = $stmt->fetch();
										$count = $stmt->rowCount();

										

										if($count > 0)
										{

											$_SESSION['username_restaurant_qRewacvAqzA'] = $username;
												$_SESSION['password_restaurant_qRewacvAqzA'] = $password;
												$_SESSION['userid_restaurant_qRewacvAqzA'] = $row['user_id'];
												header('Location: dashboard.php');
												die();
										}
										else
										{
											?>
										<div class="alert alert-danger">
							<button data-dismiss="alert" class="close close-sm" type="button">
											<span aria-hidden="true">×</span>
									</button>
							<div class="messages">
								<div>Имя пользователя и/или пароль указаны неверно!</div>
							</div>
						</div>
										<?php 
									}
					}
				?>

			

					<div class="form-input">
							<span class="txt1">Имя пользователя</span>
								<input type="text" name="username" class = "form-control username" oninput="document.getElementById('username_required').style.display = 'none'" id="user" autocomplete="off">
							<div class="invalid-feedback" id="username_required">Требуется имя пользователя!</div>
					</div>

				
			
					<div class="form-input">
							<span class="txt1">Пароль</span>
							<input type="password" name="password" class="form-control" oninput="document.getElementById('password_required').style.display = 'none'" id="password" autocomplete="new-password">
							<div class="invalid-feedback" id="password_required">Требуется пароль!</div>
					</div>

					
			
					<p>
							<button type="submit" name="admin_login" >Войти в систему</button>
					</p>

					
					
					<span class="forgotPW">Забыли свой пароль? <a href="resetPassword.php">Сбросьте его здесь.</a></span>

				</form>
		</div>

<?php include 'Includes/templates/footer.php'; ?>
