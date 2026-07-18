@extends('layouts.app')

@section('title', 'Model Training Report — Smart Discussion Forum')

@section('content')

<div class="page-head">
    <h1>🧠 Model Training Report</h1>
    <p>Shows the matrix factorization model learning over {{ count($lossHistory) }} training epochs.</p>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><span class="ic">📉</span> Loss Over Time</div>
    </div>

    @if(count($lossHistory) > 0)
        @php
            $w = 600; $h = 200; $pad = 20;
            $maxLoss = max($lossHistory);
            $minLoss = min($lossHistory);
            $range = max(0.0001, $maxLoss - $minLoss);
            $count = count($lossHistory);
            $stepX = $count > 1 ? ($w - $pad * 2) / ($count - 1) : 0;

            $points = [];
            foreach ($lossHistory as $i => $loss) {
                $x = $pad + $i * $stepX;
                $y = $h - $pad - (($loss - $minLoss) / $range) * ($h - $pad * 2);
                $points[] = round($x, 1) . ',' . round($y, 1);
            }
            $pointsStr = implode(' ', $points);
        @endphp

        <svg viewBox="0 0 {{ $w }} {{ $h }}" style="width:100%; height:220px;">
            <polyline points="{{ $pointsStr }}" fill="none" stroke="#e63946" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <div style="display:flex; justify-content:space-between; margin-top:16px; font-size:14px;">
            <div><strong>Starting Loss (Epoch 1):</strong> {{ $lossHistory[0] }}</div>
            <div><strong>Final Loss (Epoch {{ count($lossHistory) }}):</strong> {{ end($lossHistory) }}</div>
        </div>

        <div style="margin-top:20px;">
            <strong>Sample checkpoints:</strong>
            <ul style="margin-top:8px;">
                @foreach($lossHistory as $i => $loss)
                    @if($i == 0 || $i == 9 || $i == 49 || $i == 149 || $i == count($lossHistory) - 1)
                        <li>Epoch {{ $i + 1 }}: loss = {{ $loss }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @else
        <div class="empty-state">
            <p>Not enough group membership data yet to train the model. Add more users/groups first.</p>
        </div>
    @endif
</div>

@endsection