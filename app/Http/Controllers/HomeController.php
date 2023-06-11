<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class HomeController extends Controller
{
    public function test(Request $request){
        try {
            // Imprimir en la consola
            // $connector = new FilePrintConnector("php://stdout");
            // Impresión normal
            $connector = $request->platform == 'linux' ? new FilePrintConnector($request->printer_name) : new WindowsPrintConnector($request->printer_name);

            // /* Start the printer */
            $printer = new Printer($connector);
            
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            // /* Print top logo */
            $dir_logo = 'img/gerente-logo.png';
            if (file_exists($dir_logo)) {
                $logo = EscposImage::load($dir_logo);
                $printer -> bitImageColumnFormat($logo);
            }
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> setEmphasis(true);
            $printer -> text("Esto es una prueba\n");
            $printer -> setEmphasis(false);
            $printer -> selectPrintMode();
            $printer -> setUnderline(true);
            $printer -> text("gerente.rest\n");
            $printer -> setUnderline(false);
            $printer -> feed(2);

            /* Cut the receipt and open the cash drawer */
            $printer -> cut();

            $printer -> pulse();

            $printer -> close();

            return response()->json(['message' => "Impresión exitosa"]);
        } catch (\Throwable $th) {
            return response()->json(['error' => json_encode($th)]);
        }
    }

    public function print(Request $request){
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $dias = ['', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        try {
            // Imprimir en la consola
            // $connector = new FilePrintConnector("php://stdout");
            // Impresión normal
            $connector = $request->platform == 'linux' ? new FilePrintConnector($request->printer_name) : new WindowsPrintConnector($request->printer_name);
            
            /* Information for the receipt */
            $items = array();
            $subtotal_amount = 0;
            $discount_amount = $request->discount;
            foreach ($request->details as $value) {
                array_push($items, new item($value["quantity"].' '.$value["product"], number_format($value["total"], 2, '.', '')));
                $subtotal_amount += $value["total"];
            }
            $subtotal = new item('Subtotal', number_format($subtotal_amount, 2, '.', ''));
            // $tax = new item('A local tax', '1.30');
            $discount = new item('Descuento', number_format($discount_amount, 2, '.', ''));
            $total = new item('Total', number_format($subtotal_amount - $discount_amount, 2, '.', ''), true);
            /* Date is kept the same for testing */
            // $date = date('l jS \of F Y h:i:s A');
            $date = $dias[date('N')].', '.date('d \d\e ').$meses[intval(date('m'))].date(' \d\e Y h:i a');

            // /* Start the printer */
            $printer = new Printer($connector);
            
            $printer -> setJustification(Printer::JUSTIFY_CENTER);

            // /* Print top logo */
            $dir_logo = 'img/logo.png';
            if (file_exists($dir_logo)) {
                $logo = EscposImage::load($dir_logo);
                $printer -> bitImageColumnFormat($logo);
            }

            /* Name of shop */
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> setEmphasis(true);
            $printer -> text(Str::upper($request->company_name)."\n");
            $printer -> selectPrintMode();
            $printer -> text("Venta No. ".$request->sale_number."\n");
            $printer -> setUnderline(true);
            $printer -> text($request->sale_type.($request->table_number ? ' '.$request->table_number : '')."\n");
            $printer -> setUnderline(false);
            $printer -> feed();

            /* Title of receipt */
            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> text("DETALLE DE VENTA\n");
            $printer -> setEmphasis(false);

            /* Items */
            $printer -> setEmphasis(true);
            $printer -> text(new item('', 'Bs.'));
            $printer -> setEmphasis(false);
            foreach ($items as $item) {
                $printer -> text($item);
            }
            $printer -> setEmphasis(true);
            $printer -> text($subtotal);
            $printer -> setEmphasis(false);
            $printer -> feed();

            /* Tax and total */
            // $printer -> text($tax);
            $printer -> text($discount);
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text($total);
            $printer -> selectPrintMode();

            /* Footer */
            $printer -> feed(2);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("Gracias por su preferencia!\n");
            // $printer -> text("For trading hours, please visit example.com\n");
            $printer -> feed(2);
            $printer -> text($date . "\n");
            $printer -> feed();

            /* Cut the receipt and open the cash drawer */
            $printer -> cut();

            /* Imprimir comanda */
            if($request->print_kitchen_tickets){
                /* Title of receipt */
                $printer -> setEmphasis(true);
                $printer -> text("Venta No. ".$request->sale_number."\n");
                $printer -> setUnderline(true);
                $printer -> text($request->sale_type.($request->table_number ? ' '.$request->table_number : '')."\n");
                $printer -> setUnderline(false);
                $printer -> feed();
                $printer -> setJustification(Printer::JUSTIFY_LEFT);
                $printer -> text("DETALLE DE VENTA\n");
                $printer -> setEmphasis(false);

                /* Items */
                $printer -> setEmphasis(true);
                $printer -> text(new item('', 'Bs.'));
                $printer -> setEmphasis(false);
                foreach ($items as $item) {
                    $printer -> text($item);
                }
                // Subtotal
                $printer -> setEmphasis(true);
                $printer -> text($subtotal);
                $printer -> setEmphasis(false);
                $printer -> feed();

                // Observations
                if($request->observations){
                    $printer -> setEmphasis(true);
                    $printer -> text("*" . $request->observations . "\n");
                    $printer -> setEmphasis(false);
                    $printer -> feed();
                }

                // Date
                $printer -> setJustification(Printer::JUSTIFY_CENTER);
                $printer -> text($date . "\n");
                $printer -> feed();

                /* Cut the receipt and open the cash drawer */
                $printer -> cut();
            }

            $printer -> pulse();

            $printer -> close();

            return response()->json(['message' => "Impresión exitosa"]);
        } catch (\Throwable $th) {
            return response()->json(['error' => json_encode($th)]);
        }
    }
}

/* A wrapper to do organise item names & prices into columns */
class item {
    private $name;
    private $price;
    private $dollarSign;

    public function __construct($name = '', $price = '', $dollarSign = false)
    {
        $this -> name = $name;
        $this -> price = $price;
        $this -> dollarSign = $dollarSign;
    }
    
    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this -> dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this -> name, $leftCols) ;
        
        $sign = ($this -> dollarSign ? 'Bs. ' : '');
        $right = str_pad($sign . $this -> price, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}
