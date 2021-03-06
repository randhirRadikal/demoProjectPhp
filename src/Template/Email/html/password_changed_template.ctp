<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>ITIS4RENT</title>
</head>

<body style="padding:0px; margin:0px;">

<table style="width:100%; font-family:'Lucida Sans Unicode', 'Lucida Grande', sans-serif" cellpadding="0" cellspacing="0" border="0">
	<thead>
    	<tr>
        	<th style="background:#061920; width:105px; color:#FFF; text-align:left; vertical-align:middle;">
            	<p id="email_logo" style="vertical-align:middle; margin:10px 0 5px 15px; display:inline-block"><img src="<?= $this->Url->build('/',TRUE) ?>img/logo.png" alt=""></p>
            </th>
            <th style="background:#061920; color:#FFF; vertical-align:middle; text-align:left;"><span id="email_header">ITIS4RENT - Reset Password</span></th>
        </tr>
    </thead>
    <tbody>
    	<tr>
        	<td colspan="2" style="background:#DDD; text-align:left; color:#222; font-size:16px; padding:10px 15px;">

            	<h3 id="email_head"></h3>

            	<p id="email_body_first">
                    Hi <?php echo $data['first_name']; ?>,
                <p>Your password change successfully.</p>
                <p><strong>Click the "Change Password" button below to reset your password:</strong></p>
                </p>
                <p>
                    <a id="email_accept_btn" href="<?php echo $data['reset_password_link']; ?>" style="width:170px; height:32px; display:inline-block; text-align:center; border-radius:3px; line-height:32px; font-size:18px; font-family:'Trebuchet MS', Arial, Helvetica, sans-serif; text-decoration:none; color:#FFF; background:#222;">Reset Password</a>
                </p>
								<p>
                    Or, You may copy and paste this link to your browser: <?php echo $data['reset_password_link']; ?>
                </p>
                <p>Warm Regards <br> ITIS4RENT Team</p>
            </td>
        </tr>
    </tbody>
    <tfoot>
    	<tr>
        	<td colspan="2" style="background:#222; color:#FFF; text-align:center; font-size:12px;">
            	<p id="email_footer" style="padding:0px; margin:8px 0;">&copy; <?= date('Y'); ?> by ITIS4RENT</p>
            </td>
        </tr>
    </tfoot>
</table>


</body>
</html>
