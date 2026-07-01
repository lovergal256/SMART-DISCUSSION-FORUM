<!DOCTYPE html>
<html>
    <head>
        <title>Groups</title>
    </head>
<body>
    <h1>All Groups</h1>

    @if($groups->isEmpty())
       <p>No groups yet</p>
    @else
        <ul>
            @foreach($groups as $group)
               <li>{{$group['Group Name']}} - {{$group->Description}}</li>
            @endforeach
        </ul>
    @endif

    <a href="/groups/create">+Create New Group</a>
</body>
</html>