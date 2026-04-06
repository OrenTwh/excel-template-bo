<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{
    DB,
};
use Maatwebsite\Excel\Concerns\ToCollection;

use App\Models\{
    Company,
    Expense,
    FuelExpense,
    Vehicle,
};

use Helper;

use Carbon\Carbon;

class FuelExpenseImport implements ToCollection
{
    public function collection( Collection $rows ) {

        $companies = Company::where( 'status', 10 )->get();

        $fuelStation = [
            'BHP' => 1,
            'Petron' => 2,
            'Petronas' => 3,
            'Shell' => 4,
            'SURABAYA' => 5,
        ];

        foreach ( $rows as $key => $row ) {

            if ( $key == 0 ) {
                continue;    
            }

            $company = $companies->where( 'name', $row[3] )->first();
            if ( !$company ) {
                echo 'No: ' . $key . ' Company not found';
                DB::rollback();
                break;
            }
            
            $vehicle = Vehicle::where( 'license_plate', $row[4] )->first();
            if ( !$vehicle ) {
                echo 'No: ' . $key . ' Vehicle not found';
                DB::rollback();
                break;
            }

            $transactionTime = Carbon::parse( $row[6], 'Asia/Kuala_Lumpur' )->timezone( 'UTC' );

            $createFuelExpense = FuelExpense::create( [
                'company_id' => $company->id,
                'vehicle_id' => $vehicle->id,
                'location' => $row[1],
                'day' => $transactionTime->format( 'd' ),
                'month' => $transactionTime->format( 'm' ),
                'year' => $transactionTime->format( 'Y' ),
                'amount' => $row[5],
                'station' => $fuelStation[$row[1]],
                'transaction_time' => $transactionTime->format( 'Y-m-d H:i:s' ),
            ] );

            Expense::create( [
                'fuel_expense_id' => $createFuelExpense->id,
                'amount' => $createFuelExpense->amount,
                'type' => 1,
                'transaction_time' => $createFuelExpense->transaction_time,
            ] );
        }
    }
}
