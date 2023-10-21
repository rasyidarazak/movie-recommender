@extends('layouts.app')

@section('title', 'Collaborative Filtering')

@section('content')
<!-- Tampilkan rekomendasi dan MAE -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <h3 class="text-center">Rekomendasi Collaborative Filtering untuk <strong>User (ID {{ $user }})</strong></h3>
        </div>
        <div class="col-sm-6">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Judul Film</th>
                        <th>Rating Prediksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recommendations as $movie_id => $rating)
                    @php
                        $movie = DB::table('movies')->where('id', $movie_id)->first();
                        $movie_name = $movie ? $movie->title : "Film tidak ditemukan";
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $movie_name }}</td>
                        <td class="text-center">{{ $rating }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3">Mean Absolute Error (MAE): {{ $mae }}</p>
        </div>
    </div>
</div>
@endsection