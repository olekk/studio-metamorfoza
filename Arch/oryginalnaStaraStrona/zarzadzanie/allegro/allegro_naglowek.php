              <div class="info_content">
                <div id="pozycje_ikon">
                  <div>
                    <div><a class="usun" href="allegro/allegro_logowanie.php?wyloguj=ok">Wyloguj z Allegro</a></div><br />
                    <div class="serwer">Serwer Allegro : <?php echo ( $allegro->polaczenie['CONF_SANDBOX'] == 'nie' ? '<span class="zielony">RZECZYWISTY</span>' : '<span class="czerwony">TESTOWY</span>' ) ; ?></div>
                  </div>
                </div>
                <div style="float:right;padding-left:10px;">
                  <table class="allegro" style="float:right">
                    <tr><td class="allegro">Data logowania:</td><td align="left" class="allegro"><?php echo (isset($allegro->data_logowania) && $allegro->data_logowania != '' ? date("H:i:s d-m-Y", $allegro->data_logowania) : ''); ?></td></tr>
                    <tr><td class="allegro">Login:</td><td align="left" class="allegro"><?php echo (isset($_SESSION['allegro_user_login']) ? $_SESSION['allegro_user_login'] : ''); ?></td></tr>
                    <tr><td class="allegro">ID Allegro:</td><td align="left" class="allegro"><?php echo (isset($_SESSION['allegro_user_id']) ? $_SESSION['allegro_user_id'] : ''); ?></td></tr>
                  </table>
                </div>
                <div>
                  <table style="float:right">
                    <tr>
                      <td class="allegro">Wersja komponentów</td>
                      <td class="allegro">Lokalnie</td>
                      <td class="allegro">Allegro</td>
                    </tr>
                    <tr>
                      <td class="allegro">Kategorie:</td>
                      <td class="allegro"><?php echo $allegro->polaczenie['CONF_CATEGORIES_WER']; ?></td>
                      <td class="allegro"><?php echo $allegro->doGetSysStatus('3'); ?></td>
                    </tr>
                    <tr>
                      <td class="allegro">Pola formularzy:</td>
                      <td class="allegro"><?php echo $allegro->polaczenie['CONF_FIELDS_WER']; ?></td>
                      <td class="allegro"><?php echo $allegro->doGetSysStatus('4'); ?></td>
                    </tr>
                  </table>
                </div>
                <div style="clear: both;"></div>
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:100%;" />
              </div>
