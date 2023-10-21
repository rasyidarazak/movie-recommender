@extends('layouts.app')

@section('title', 'Content Based Filtering')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <h3 class="text-center">
                Rekomendasi Content Based Filtering untuk film <br><strong>{{ $movie_title }} (ID {{ $movie_id }})</strong> -
                @php
                    $movie_genre = DB::table('genre_movie')->where('movie_id', $movie_id)->get();
                @endphp
                @if (count($movie_genre) > 0)
                @foreach ($movie_genre as $genre)
                    @php
                        $genre_info = DB::table('genres')->where('id', $genre->genre_id)->first();
                        $genre_name = $genre_info ? $genre_info->genre : "Genre tidak ditemukan";
                    @endphp
                    <span class="badge rounded-pill text-bg-secondary">{{ $genre_name }}</span>
                @endforeach
            @endif
            </h3>
        </div>
        <div class="col-sm-6">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Genre Prediksi</th>
                        {{-- <th>Rating Prediksi</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recommendations as $genre_id => $rating)
                        @php
                            $genre = DB::table('genres')->where('id', $genre_id)->first();
                            $genre_name = $genre ? $genre->genre : "Genre tidak ditemukan";
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $genre_name }}</td>
                            {{-- <td class="text-center">{{ $rating }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3">Mean Absolute Error (MAE): {{ $mae }}</p>
        </div>
    </div>
</div>
@endsection