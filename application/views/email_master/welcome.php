<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
    <tr>
      <td bgcolor="#ffffff" align="center" valign="top" colspan="2"
          style="padding: 20px 20px 0px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
          <h1 style="font-size: 32px; font-weight: 400; margin: 2;">
            ¡Tu tienda ha sido creada!
          </h1>
          <p style="font-size:1rem">
            <?php echo $welcome_msg; ?>
          </p>
      </td>
    </tr>
    <tr>
      <td bgcolor="#ffffff" align="center" valign="top"
          style="padding: 20px 20px 100px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
          <a href="https://www.promocionalesenlinea.com" style="
              background: <?php echo $color1; ?>;
              padding: 1rem;
              border-radius: 1rem;
              line-height: 0.5rem;cursor:pointer;
              margin:12px; text-decoration: none;
              color: <?php echo $color1_text; ?>;
              font-size: 1rem;">
            Configura tu tienda
          </a>
          <br>
          <br>
          <!--
          <a href="https://www.promocionalesenlinea.com/<?php echo $store_path; ?>" style="
              background: <?php echo $color1; ?>;
              padding: 1rem;
              border-radius: 1rem;
              line-height: 0.5rem;cursor:pointer;
              margin:12px; text-decoration: none;
              color: <?php echo $color1_text; ?>;
              font-size: 1rem;">
            Visita tu tienda
          </a>
          -->
          <a href="https://vimeo.com/816272814" style="
              background: <?php echo $color1; ?>;
              padding: 1rem;
              border-radius: 1rem;
              line-height: 0.5rem;cursor:pointer;
              margin:12px; text-decoration: none;
              color: <?php echo $color1_text; ?>;
              font-size: 1rem;">
            Video de capacitación
          </a>
      </td>
    </tr>
    <tr>
      <td bgcolor="#ffffff" align="center" valign="top"
          style="padding: 20px 20px 100px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">

          <?php
          if (isset($file_url) && $file_url != ""){
            ?>
            <a href="<?php echo $file_url; ?>" style="
              padding: 1rem;
              border-radius: 1rem;
              line-height: 0.5rem;cursor:pointer;
              margin:12px; text-decoration: none;
              font-size: 1rem;">
            Aviso de privacidad
          </a>
          <?php
        }
         ?>
      </td>
    </tr>
  </table>
