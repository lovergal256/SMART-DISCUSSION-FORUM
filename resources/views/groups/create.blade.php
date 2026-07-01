<!DOCTYPE html>
<html>
<head>
    <title>Create Group</title>
</head>
<body>
    <h1>Create New Group</h1>

    @if($errors->any())
        <ul style="color:red;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="/groups" method="POST">
        @csrf
        <div>
            <label>Group Name</label><br>
            <input type="text" name="group_name" value="{{ old('group_name') }}">
        </div>
        <br>
        <div>
            <label>Description</label><br>
            <textarea name="Description">{{ old('Description') }}</textarea>
        </div>
        <br>
        <button type="submit">Create Group</button>
    </form>

    <br>
    <a href="/groups">← Back to Groups</a>
</body>
</html>