<form action="{{ route('logout') }}" method="post">
    @csrf
    <button class="btn btn-primary flat-btn">Logout</button>
</form>