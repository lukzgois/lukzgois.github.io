@extends('_layouts.master')

@section('body')
  <div class="mt-4 mx-2 flex pb-4">
    <img src="/images/avatar.jpg" class="rounded-full h-16 w-16 border border-grey" alt="Avatar Image" />
    <p class="ml-4 flex flex-col justify-center text-sm">
      <a href="/" class="no-underline hover:underline">Lucas Padilha Gois</a>
      <span class="text-xs text-grey mt-1">{{ date('d/m/Y', $page->date) }}</span>
    </p>
  </div>

  <div class="mx-2">
    <h1 class="text-grey-darkest mb-2">{{ $page->title }}</h1>

    <div class="post-content">
      @yield('content')

      <a href="/" class="text-center">⬅ Voltar</a>
    </div>


  </div>
@endsection
