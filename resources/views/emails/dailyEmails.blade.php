<h2>Tasks due today </h2> 
@foreach($due as $due)
<p>{{$due['title']}}</p>
@endforeach
<h2>Tasks OverDue today </h2>
@foreach($overdue as $due)
<p>{{$due['title']}}</p>
@endforeach

