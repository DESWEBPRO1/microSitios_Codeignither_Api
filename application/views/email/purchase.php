<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
    <tr>
        <td bgcolor="#ffffff" align="center" valign="top"
            style="padding: 20px 20px 0px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
            <h1 style="font-size: 32px; font-weight: 400; margin: 2;">
              Pedido #<?php echo $insert_id; ?>
            </h1>
        </td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
            <p style="margin: 0;">
              Nombre: <?php echo $info['client']['firstName']; ?>
              <br>
              Empresa: <?php echo $info['client']['company']; ?>
              <br>
              Correo electrónico: <?php echo $info['client']['email']; ?>
              <br>
              Teléfono: <?php echo $info['client']['phone']; ?>
              <?php
              if ($info['client']['state'] != ''){
                ?>
               <br>
               Estado: <?php echo $info['client']['state']; ?>
                <?php
              }
               ?>
               <?php
               if ($info['client']['city'] != ''){
                 ?>
               <br>
               Ciudad: <?php echo $info['client']['city']; ?>
                 <?php
               }
                ?>
                <?php
                if (isset($comments) && $comments != ''){
                ?>
                <br>
                Comentario: <?php echo $comments; ?>
                <?php
                }
                ?>
              <br>
            </p>
        </td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" align="left">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                        <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                          <thead>
                            <tr bgcolor="<?php echo $color1; ?>" style="border-radius: 3px;" >
                              <td align="center"  colspan="3" style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                Producto
                              </td>
                              <?php

                              if ($store['display_price'] == true){
                                ?>
                                <td align="center" style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                 Precio
                                </td>
                                 <td align="center" style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                   Subtotal
                                 </td>
                                <?php
                              }

                               ?>
                            </tr>
                          </thead>
                          <tbody>
                            <?php

                            foreach ($info['products'] as $product) {
                              ?>
                              <tr >
                                <td align="center" style="border-radius: 3px;">
                                  x <?php echo $product['cantidad']; ?>
                                </td>
                                <td align="center" style="border-radius: 3px;">
                                  <?php
                                  if (!isset($product['imagen'])){
                                    $product['imagen'] = "";
                                  }
                                   ?>
                                  <img src="<?php echo $product['imagen']; ?>" width="50" height="50">
                                </td>
                                <td align="center" style="border-radius: 3px;">
                                  <?php echo $product['codigo']; ?>
                                </td>

                                <?php

                                if ($store['display_price'] == true){
                                  ?>
                                  <td align="center" style="border-radius: 3px;">
                                    <?php echo $product['precio_unitario']; ?>
                                  </td>
                                  <td align="center" style="border-radius: 3px;">
                                    <?php echo $product['total']; ?>
                                  </td>
                                <?php
                                }
                                ?>
                              </tr>
                              <?php
                            }
                            if ($store['display_price'] == true){
                              if (isset($subtotal) && $subtotal != ""){
                                ?>
                                <tr>
                                  <td align="center"  colspan="4" bgcolor="<?php echo $color1; ?>"   style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                    Subtotal
                                  </td>
                                  <td align="center" >
                                    <?php echo $currency['sign_before'] .number_format($info['subtotal'],2,".",","). $currency['sign_after']; ?>
                                  </td>
                                </tr>
                                <tr>
                                  <td align="center" colspan="4" bgcolor="<?php echo $color1; ?>"   style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                    I.V.A. (<?php echo $pct_iva; ?>%)
                                  </td>
                                  <td align="center" >
                                    <?php echo $currency['sign_before']. number_format($info['iva'],2,".",",") . $currency['sign_after']; ?>
                                  </td>
                                </tr>
                                <?php
                              }
                               ?>

                               <tr>
                                 <td align="center" colspan="4" bgcolor="<?php echo $color1; ?>"  style="color:<?php echo $color1_text; ?>;padding:4px;font-weight:bold;">
                                   Total
                                 </td>
                                 <td align="center">
                                   <?php echo $currency['sign_before']. number_format($info['total'],2,".",",") . $currency['sign_after']; ?>
                                 </td>
                               </tr>
                               <?php
                            }
                            ?>
                          </tbody>
                        </table>
                    </td>
                </tr>
            </table>
          </td>
      </tr>
  </table>
