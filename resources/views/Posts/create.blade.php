@extends('layouts.app')

@section('content')
    <h1>Add Post to: {{ $topic->title }}</h1>

    <div class="card">
        <form action="{{ route('topics.posts.store', $topic->TopicID) }}" method="POST">
            @csrf

            <label>Your Post</label>
            <textarea name="body" rows="6" placeholder="Write your post...">{{ old('body') }}</textarea>
            @error('body')
                <p style="color:red">{{ $message }}</p>
            @enderror
            {{-- Three dot menu for visibility --}}
    <div style="position:relative; display:inline-block; margin-bottom:15px;">
         <button type="button" onclick="toggleMenu()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#0077b6;">⋮</button>
        <div id="visibilityMenu" style="display:none; position:absolute; background:white; border:1px solid #b0c4d8; border-radius:6px; padding:10px; min-width:220px; box-shadow:0 2px 8px rgba(0,0,0,0.15); z-index:100;">
          <p style="font-weight:600; margin:0 0 10px; color:#0077b6;">Who can see this post?</p>
        
         <label style="display:block; margin-bottom:8px;">
            <input type="radio" name="visibility" value="everyone" checked> 🌍 Everyone
         </label>
        
         <label style="display:block; margin-bottom:8px;">
            <input type="radio" name="visibility" value="only_share_with"> 👥 Only share with
         </label>
           <div id="onlyShareWith" style="display:none; margin:8px 0 8px 20px;">
              @foreach(\App\Models\User::all() as $user)
                  <label style="display:block;">
                     <input type="checkbox" name="share_with_users[]" value="{{ $user->UserID }}"> {{ $user->FullName }}
                  </label>
              @endforeach
            </div>

          <label style="display:block; margin-bottom:8px;">
            <input type="radio" name="visibility" value="exclude"> 🚫 Exclude from
          </label>
        <div id="excludeUsers" style="display:none; margin:8px 0 8px 20px;">
            @foreach(\App\Models\User::all() as $user)
                <label style="display:block;">
                    <input type="checkbox" name="excluded_users[]" value="{{ $user->UserID }}"> {{ $user->FullName }}
                </label>
            @endforeach
        </div>
    </div>
</div>

<script>
function toggleMenu() {
    var menu = document.getElementById('visibilityMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('change', function(e) {
    if(e.target.name === 'visibility') {
        document.getElementById('onlyShareWith').style.display = 
            e.target.value === 'only_share_with' ? 'block' : 'none';
        document.getElementById('excludeUsers').style.display = 
            e.target.value === 'exclude' ? 'block' : 'none';
    }
});
</script>

            <button type="submit" class="btn">Submit Post</button>
            <a href="{{ route('topics.show', $topic->TopicID) }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection
