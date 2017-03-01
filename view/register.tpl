<h3>Registration</h3>

<form action="register" method="post" >

	$registertext

	<div id="register-name-wrapper" >
		<label for="register-name" id="label-register-name" >Your Full Name (e.g. Joe Smith): </label>
		<input type="text" maxlength="60" size="32" name="username" id="register-name" value="" >
	</div>
	<div id="register-name-end" ></div>


	<div id="register-email-wrapper" >
		<label for="register-email" id="label-register-email" >Your Email Address: </label>
		<input type="text" maxlength="60" size="32" name="email" id="register-email" value="" >
	</div>
	<div id="register-email-end" ></div>

	<p id="register-nickname-desc" >
	Choose a profile nickname. This must begin with a text character.
	Your global profile locator will then be '<strong>nickname@$sitename</strong>'.
	</p>
	<div id="register-nickname-wrapper" >
		<label for="register-nickname" id="label-register-nickname" >Choose a nickname: </label>
		<input type="text" maxlength="60" size="32" name="nickname" id="register-nickname" value="" ><div id="register-sitename">@$sitename</div>
	</div>
	<div id="register-nickname-end" ></div>



	<div id="register-password-wrapper" >
		<label for="register-password" id="label-register-password" >Choose a password: </label>
		<input type="password" maxlength="60" size="32" name="password" id="register-password" value="" >
	</div>
	<div id="register-password-end" ></div>

	<div id="register-verify-wrapper" >
		<label for="register-verify" id="label-register-verify" >Verify password: </label>
		<input type="password" maxlength="60" size="32" name="verify" id="register-verify" value="" >
	</div>
	<div id="register-verify-end" ></div>


	<div id="register-submit-wrapper">
		<input type="submit" name="submit" id="register-submit-button" value="Register" />
	</div>
	<div id="register-submit-end" ></div>
</form>
