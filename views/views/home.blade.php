@extends('layouts.app')

@section('content')
<div class="container">
    @can('lms dashboard')
     <div class="row section-mg row-md-body no-nav mt-5">
        <div class="col-md-6 col-lg-3">
            <div class="card card_bg">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="card-body card_bg_body">
                    <h4>Total Books per Office</h4>
                        <ul class="scrollable-content">
                            @foreach($booksPerOffice as $office)
                                <a href="{{ url('books') . '?office_id=' . $office->id }}"><li>{{ $office->name }}({{ $office->address }}): {{ $office->total_books }} </li></a>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
        

        <div class="col-md-6 col-lg-3">
            <div class="card card_bg">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="card-body card_bg_body">
                    <h3>Total Books per Shelf</h3>
                    <ul class="scrollable-content">
                        @foreach($booksPerShelf as $shelf)
                            <a href="{{ url('books') . '?bookshelves_id=' . $shelf->id }}"><li>BookShelf No {{ $shelf->number }}: {{ $shelf->total_books }} </li></a>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card card_bg">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="card-body card_bg_body">
                    <h3>Total Issued Books per Office</h3>
                         <ul class="scrollable-content">
                            @foreach($issuedBooksPerOffice as $office)
                                <a href="{{ url('offices/'.$office->office_id.'/issue/books/') }}"><li>{{ $office->name }}({{ $office->address }}): {{ $office->total_issued }} </li></a>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card card_bg">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="card-body card_bg_body">
                    <h3>Total Available Books per Office</h3>
                        <ul class="scrollable-content">
                            @foreach($availableBooksPerOffice as $office)
                                <a href="{{ url('offices/available/books/'.$office->id.'/list') }}"><li>{{ $office->name }}({{ $office->address }}): {{ $office->total_available }} </li></a>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
