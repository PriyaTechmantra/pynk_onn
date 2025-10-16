@extends('layouts.app')
@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Create Catalogue
                            <a href="{{ url('catalogues') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                         <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('catalogues.store')}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Add New Catalogue</h4>
                                    <div class="mb-3">
                                        <label class="label-control">Name <span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{old('title')}}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Start Date </label>
                                        <input type="date" name="start_date" class="form-control">{{old('start_date')}}</textarea>
                                        @error('start_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">End Date </label>
                                        <input type="date" name="end_date" class="form-control">{{old('end_date')}}</textarea>
                                        @error('end_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">State </label>
                                        <input type="text" name="state" class="form-control">{{old('state')}}</textarea>
                                        @error('state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">VP </label>
                                        <input type="text" name="vp" class="form-control">{{old('vp')}}</textarea>
                                        @error('vp') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-12">
                                        <div class="row">
                                            <div class="col-md-6 card">
                                                <div class="card-header p-0 mb-3">Image <span class="text-danger">*</span></div>
                                                <div class="card-body p-0">
                                                    <div class="w-100 product__thumb">
                                                        <label for="icon"><img id="iconOutput" src="{{ asset('admin/images/placeholder-image.jpg') }}" /></label>
                                                    </div>
                                                    <input type="file" name="image" id="icon" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                                    <script>
                                                        let loadIcon = function(event) {
                                                            let iconOutput = document.getElementById('iconOutput');
                                                            iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                                            iconOutput.onload = function() {
                                                                URL.revokeObjectURL(iconOutput.src) // free memory
                                                            }
                                                        };
                                                    </script>
                                                </div>
                                                @error('image') <p class="small text-danger">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="col-md-6 card">
                                                <div class="card-header p-0 mb-3">Pdf <span class="text-danger">*</span></div>
                                                <div class="card-body p-0">
                                                    <div class="w-100 product__thumb">
                                                    </div>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" type="file" name="pdf" id="pdf">
                                                </div>
                                                </div>
                                                @error('pdf') <p class="small text-danger">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-danger">Add New Catalogue</button>
                                    </div>
                                </form>
                            </div>
                                   
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
@section('script')
<script>
    function htmlToCSV() {
        var data = [];
        var rows = document.querySelectorAll("#example5 tbody tr");
        @php
            if (!request()->input('page')) {
                $page = '1';
            } else {
                $page = request()->input('page');
            }
        @endphp

        var page = "{{ $page }}";

        data.push("SRNO,Image,Pdf,Title,Date,Status");

        for (var i = 0; i < rows.length; i++) {
            var row = [],
                cols = rows[i].querySelectorAll("td");

            for (var j = 0; j < cols.length; j++) {
                var text = cols[j].innerText.split(' ');
                var new_text = text.join('-');
                if (j == 3||j==4)
                    var comtext = new_text.replace(/\n/g, "-");
                else
                    var comtext = new_text.replace(/\n/g, ";");
                row.push(comtext);

            }
            data.push(row.join(","));
        }

        downloadCSVFile(data.join("\n"), 'Catalogue.csv');
    }

    function downloadCSVFile(csv, filename) {
        var csv_file, download_link;

        csv_file = new Blob([csv], {
            type: "text/csv"
        });

        download_link = document.createElement("a");

        download_link.download = filename;

        download_link.href = window.URL.createObjectURL(csv_file);

        download_link.style.display = "none";

        document.body.appendChild(download_link);

        download_link.click();
    }


</script>
 @if (request()->input('export_all') == true)
                <script>
                    htmlToCSV();
                    window.location.href = "{{ route('catalogue.index') }}";
                </script>
            @endif
@endsection
