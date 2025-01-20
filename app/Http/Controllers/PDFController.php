<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Models\WaterConnection;
use Carbon\Carbon;
use Illuminate\Http\Request; // For handling the image
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PDFController extends Controller
{
    public function generateBarangayID(Request $request)
    {

        $data = [
            'name' => $request?->user['name'] ?? 'Default Name',
            'purok' => $request->purok,
            'date_of_birth' => $request->date_of_birth,
            'age' => $request->age,
            'civil_status' => $request->civil_status,
            'citizenship' => $request->citizenship,
            'gender' => $request->gender,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_type' => $request->blood_type,
            'emergency_name' => $request->emergency_name,
            'emergency_relation' => $request->emergency_relation,
            'emergency_address' => $request->emergency_address,
            'emergency_contact_number' => $request->emergency_contact_number,
        ];

        $pdf = \PDF::loadView('pdf.barangay_id', $data);

        return $pdf->stream('barangay_id.pdf');
    }

    public function generateBarangayClearance(Request $request)
    {
        $data = [
            'name' => $request->name,
            'civil_status' => $request->civil_status,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->address,
<<<<<<< HEAD
            // 'certificate_number' => $request->certificate_number,
            'cert_no' => $request->cert_no,
=======
            'certificate_number' => $request->certificate_number,
>>>>>>> ecc6800919e4cc485cb711403da1aaf9ed85b67a
            'purpose' => $request->purpose,
            'imagePath' => storage_path('app/images/clearance.png'),
        ];

        $pdf = \PDF::loadView('pdf.barangay_clearance', $data);

        return $pdf->stream('barangay_clearance.pdf');
    }

    public function viewDocument(RequestDocument $requestDocument)
    {
        $document = $requestDocument->file_path;

        $file = Storage::get($document);

        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename='.$document);
    }

    public function generateBillPDF(Bill $bill, Request $request)
    {

        $bills = Bill::where('status', 'partial')
            ->where('id', '<', $bill->id)
            ->get();

        $payment = Payment::where('bill_id', $bill->id)->first();

        $partialValue = 0;

        foreach ($bills as $bill) {
            $difference = $bill->billing_amount - $bill->partial_payment;
            $partialValue += $difference;
        }

        $billData = $bill->load('payment', 'reading', 'waterConnection');

        $auth = auth()->user();

        $excess = $this->calculateExcessCharge($bill->reading->total_consumption, $bill->minimumConsumption, $bill->exceedChargePerUnit);

        $period = $this->getLastMonthPeriod($billData->created_at);

        $total = $bills ? $bill->billing_amount + $partialValue : $bill->billing_amount;

        $data = [
            'account_number' => $billData->waterConnection->reference_id,
            'consumer_name' => auth()->user()->isAdmin() ? $billData->waterConnection->name : $auth->name,
            'consumer_number' => $auth->consumer_number,
            'purok' => $auth->purok,
            'bill_date' => $billData->created_at->format('F j, Y'),
            'excess_key' => $excess['excess'],
            'excess_charge' => $excess['excessCharge'],
            'total_charge' => $bill->billing_amount,
            'period_from' => $period['start'],
            'period_to' => $period['end'],
            'total' => $billData->is_discounted ? $total - ($total * .05) : $total,
            'paid_amount' => $payment->partial_payment,
            'cut_off_date' => $this->getDiscountCutOffDate($billData->created_at),
            'monthly_consumption' => $billData->waterConnection->img_uri_consumption,
            'monthly_spending' => $billData->waterConnection->img_uri_spending,
            'previous_balance' => $partialValue,
            'partial' => $billData->partial_payment,
            'is_discounted' => $billData->is_discounted ? 'YES' : '-',
            'minimumConsumption' => $bill->minimumConsumption,
            'minimumValue' => $bill->minimum,
        ];

        $pdf = \PDF::loadView('pdf.bill', $data);

        return $pdf->stream('bill.pdf');
    }

    public function calculateExcessCharge($consumption, $minimumConsumption, $exceedChargePerUnit)
    {
        if ($consumption > $minimumConsumption) {
            $excess = $consumption - $minimumConsumption;
            $excessCharge = $excess * $exceedChargePerUnit;

            return [
                'excess' => $excess,
                'excessCharge' => $excessCharge,
            ];
        }

        return [
            'excess' => 0,
            'excessCharge' => 0,
        ];
    }

    public function getLastMonthPeriod($date)
    {
        $currentDate = Carbon::parse($date);

        $startOfLastMonth = $currentDate->copy()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = $currentDate->copy()->subMonth()->endOfMonth()->toDateString();

        return [
            'start' => $startOfLastMonth,
            'end' => $endOfLastMonth,
        ];
    }

    public function getDiscountCutOffDate($date)
    {
        $carbonDate = Carbon::parse($date);

        $firstDayOfMonth = $carbonDate->copy()->startOfMonth();

        $cutOffDate = $firstDayOfMonth->addDays(14)->format('F j, Y');

        return $cutOffDate;
    }

    public function saveChart(Request $request)
    {
        $this->saveURI($request->waterConnectionId, $request->imgURI, 'consumption');

        return response()->json(['status' => 'success']);
    }

    public function saveSpendingChart(Request $request)
    {
        $this->saveURI($request->waterConnectionId, $request->imgURI, 'spending');

        return response()->json(['status' => 'success']);
    }

    public function saveURI(string $id, string $imgURI, $type)
    {
        $waterConnection = WaterConnection::find($id);

        $type === 'consumption' ? $waterConnection->img_uri_consumption = $imgURI : $waterConnection->img_uri_spending = $imgURI;

        $waterConnection->save();
    }
}
