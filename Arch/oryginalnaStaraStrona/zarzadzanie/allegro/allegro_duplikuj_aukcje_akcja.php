<?php
if ( isset($id_ponowne_aukcji) || isset($id_ponowne_allegro) ) {

    $allegro = new Allegro(true, true);

    if ( isset($id_ponowne_allegro) ) { 
        //
        $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '".$id_ponowne_allegro."'";
        //
      } else {
        //
        $zapytanie = "SELECT * FROM allegro_auctions WHERE auction_id = '".$id_ponowne_aukcji."'";
        //      
    }
    
    $sql = $db->open_query($zapytanie);

    $rezultat = '';
    $pola = array();

    if ( $db->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();
        
        $id_aukcji         = array(floatval($info['auction_id']));
        $czas_rozpoczecia  = '0';
        $czas_trwania      = (strtotime($info['products_date_end']) - strtotime($info['products_date_start']))/(60*60*24);
        $data_start        = time();
        $local_id          = substr(time(), -6) . substr($info['auction_id'], -2);
        $data_koniec       = $data_start + ( 60*60*24*$czas_trwania ) ;
        $kategoria_sklepu  = $info['allegro_category_shop'];
        
        if ( $kategoria_sklepu == '' || $kategoria_sklepu == '0' ) {
          $kategoria_sklepu = '';
        }
        
        $typ_aukcji = $info['auction_type'];

        $pola = array(
                array('auction_id',$local_id),
                array('products_id',$info["products_id"]),
                array('products_name',$info['products_name']),
                array('allegro_category',$info['allegro_category']),
                array('allegro_category_shop',$info['allegro_category_shop']),
                array('products_quantity',$info['products_quantity']),
                array('auction_quantity',$info['products_quantity']),
                array('auction_price',$info['auction_price']),
                array('auction_seller',(isset($_SESSION['allegro_user_login'])?$_SESSION['allegro_user_login']:'')),
                array('products_date_start',date("Y-m-d H:i:s", $data_start)),
                array('products_date_end',date("Y-m-d H:i:s", $data_koniec)),
                array('products_stock_attributes',$info['products_stock_attributes']),
                array('allegro_server',$info['allegro_server']),
                array('auction_buy_now',$info['auction_buy_now']),
                array('auction_source','1'),
                array('auction_status','-1'),
                array('auction_type',$info['auction_type']),
                array('auction_date_start',date("Y-m-d H:i:s", $data_start)),
                array('auction_date_end',date("Y-m-d H:i:s", $data_koniec)),
                array('synchronization',1));

        if ( $typ_aukcji == '0' ) {

          $rezultat = $allegro->doSellSomeAgain( $id_aukcji, $czas_rozpoczecia, $czas_trwania, '2', $local_id );
          
        } else {

          $rezultat = $allegro->doSellSomeAgainInShop( $id_aukcji, $czas_rozpoczecia, '30', '2', '0', $kategoria_sklepu, $local_id );
          
        }

        unset($data_start, $local_id);
      
    }

    $db->close_query($sql);
    unset($zapytanie);

    unset($allegro);
    
}    
?>