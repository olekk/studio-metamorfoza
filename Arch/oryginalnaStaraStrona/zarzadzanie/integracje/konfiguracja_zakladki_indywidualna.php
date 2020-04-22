<div class="sledzenie">

  <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="<?php echo $nazwa; ?>Form" class="cmxform">
  
    <div>
        <input type="hidden" name="akcja" value="zapisz" />
        <input type="hidden" name="system" value="<?php echo $nazwa; ?>" />
    </div>
    
    <div class="obramowanie_tabeliSpr">
    
        <table class="listing_tbl">
        
          <tr class="div_naglowek">
            <td style="text-align:left" colspan="2">Indywidualna zakładka definiowana przez użytkownika</td>
          </tr>
          
          <tr><td colspan="2" class="sledzenie_opis">
            <div>Wyświetla wysuwaną zakładkę zdefiniowaną przez administratora sklepu.</div>
          </td></tr>                  
        
          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label>Włącz zakładkę Indywidualną nr <?php echo $nr; ?>:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper($nazwa) . '_WLACZONA']['1'], $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_WLACZONA']['0'], 'zakladka_' . $nazwa . '_wlaczona', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_WLACZONA']['2'], '', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_WLACZONA']['3'] );
              ?>
            </td>
          </tr>
          
          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label class="required">Obrazek wysuwanej zakładki:</label>
            </td>
            <td>
              <?php
              echo '<input type="text" id="zakladka_' . $nazwa . '_ikona" name="zakladka_' . $nazwa . '_ikona" value="'.$parametr['ZAKLADKA_' . strtoupper($nazwa) . '_IKONA']['0'].'" size="53" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser(\'zakladka_' . $nazwa . '_ikona\',\'\',\'' . KATALOG_ZDJEC . '\')" />';                        
              ?>
              <span class="usun_zdjecie toolTipTopText" data="zakladka_<?php echo $nazwa; ?>_ikona" title="Usuń przypisany obrazek"></span>
              <label class="error" style="display:none" for="zakladka_<?php echo $nazwa; ?>_ikona">To pole jest wymagane.</label>
              
              <div id="divzakladka_<?php echo $nazwa; ?>_ikona" style="padding-top:10px;display:none">
                <span id="fozakladka_<?php echo $nazwa; ?>_ikona">
                    <span class="zdjecie_tbl">
                        <img src="obrazki/_loader_small.gif" alt="" />
                    </span>
                </span> 

                <?php if (!empty($parametr['ZAKLADKA_' . strtoupper($nazwa) . '_IKONA']['0'])) { ?>
                <script type="text/javascript">
                //<![CDATA[            
                pokaz_obrazek_ajax('zakladka_<?php echo $nazwa; ?>_ikona', '<?php echo $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_IKONA']['0']; ?>')
                //]]>
                </script> 
                <?php } ?>   
                
              </div> 
          
            </td>
          </tr>  

          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label>Treść wysuwanej zakładki:</label>
            </td>
            <td>
            
              <script type="text/javascript">
              //<![CDATA[
              $(document).ready(function() {
                  ckedit('zakladka_<?php echo $nazwa; ?>_tresc','90%','150px');
              });
              //]]>
              </script>                       
            
              <?php
              echo '<textarea name="zakladka_' . $nazwa . '_tresc" id="zakladka_' . $nazwa . '_tresc" cols="70" rows="5">'.$parametr['ZAKLADKA_' . strtoupper($nazwa) . '_TRESC']['0'].'</textarea>';                        
              ?>
            </td>
          </tr> 

          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label class="required">Szerokość pola z treścią zakładki:</label>
            </td>
            <td>
              <?php
              echo '<input type="text" id="zakladka_' . $nazwa . '_szerokosc" name="zakladka_' . $nazwa . '_szerokosc" value="'.$parametr['ZAKLADKA_' . strtoupper($nazwa) . '_SZEROKOSC']['0'].'" size="5" /> px';                        
              ?>
              <label class="error" style="display:none" for="zakladka_<?php echo $nazwa; ?>_szerokosc">To pole jest wymagane. Wartość może być tylko jako liczba całkowita.</label>
            </td>
          </tr>                     

          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label>Widoczna dla wersji językowej:</label>
            </td>
            <td>
              <?php
              $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
              echo Funkcje::RozwijaneMenu('zakladka_' . $nazwa . '_jezyk',$tablica_jezykow,$parametr['ZAKLADKA_' . strtoupper($nazwa) . '_JEZYK']['0']);
              unset($tablica_jezykow);
              ?>                         
            </td>
          </tr>                       
          
          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label>Strona po której ma się wyświetlać zakładka:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper($nazwa) . '_STRONA']['1'], $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_STRONA']['0'], 'zakladka_' . $nazwa . '_strona', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_STRONA']['2'], '', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_STRONA']['3'] );
              ?>
            </td>
          </tr>   
          
          <tr class="pozycja_off">
            <td style="width:225px;padding-left:25px">
              <label>Kolejność wyświetlania na stronie:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper($nazwa) . '_SORT']['1'], $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_SORT']['0'], 'zakladka_' . $nazwa . '_sort', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_SORT']['2'], '', $parametr['ZAKLADKA_' . strtoupper($nazwa) . '_SORT']['3'] );
              ?>
            </td>
          </tr>                    

          <tr>
            <td colspan="2">
              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == $nazwa ? $wynik : '' ); ?>
              </div>
            </td>
          </tr>
        </table>

    </div>
  </form>
  
</div> 