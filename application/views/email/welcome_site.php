<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
    <tr>
      <td bgcolor="#ffffff" align="center" valign="top" colspan="2"
          style="padding: 20px 20px 0px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
          <h1 style="font-size: 28px; font-weight: 400; margin: 2;">
            ¡Tu tienda ha sido creada!
          </h1>
          <?php
          if ($password != ""){
            ?>
            <p style="font-size:16px">
              Contraseña:<br>
              <span style="
                font-weight: bold;
                border: 1px solid black;
                padding: 14px;">
                <?php echo $password; ?>
              </span>
            </p>
            <?php
          }
           ?>
      </td>
    </tr>
    <tr>
      <td bgcolor="#ffffff" align="center" valign="top"
          style="padding: 20px 20px 100px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
          <a href="<?php echo $site_url.$store_path; ?>" style="
              background: <?php echo $color1; ?>;
              padding: 1rem;
              border-radius: 1rem;
              line-height: 0.5rem;cursor:pointer;
              margin:12px; text-decoration: none;
              color: <?php echo $color1_text; ?>;">
              <span style="font-size: 16px;">
                Ingresa a tu tienda
              </span>
          </a>
          <br>
      </td>
    </tr>
  </table>
