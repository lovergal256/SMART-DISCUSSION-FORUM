@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Warnings & Account Status</h2>

    @if($activeBlacklist)
        <div class="alert alert-danger">
            <strong>Account Blocked</strong><br>
            Reason: {{ $activeBlacklist->Reason }}<br>
            Blocked from {{ $activeBlacklist->StartDate }} until {{ $activeBlacklist->EndDate }}.
        </div>
    @endif

    @if($warnings->isEmpty())
        <p>You have no warnings. Keep up the activity!</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Warning #</th>
                    <th>Date Issued</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warnings as $warning)
                    <tr>
                        <td>{{ $warning->WarningNumber }}</td>
                        <td>{{ \Carbon\Carbon::parse($warning->WarningDate)->format('M d, Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection