@include('header')
@foreach($charts as $chart)
    @include('chart',$chart)
@endforeach