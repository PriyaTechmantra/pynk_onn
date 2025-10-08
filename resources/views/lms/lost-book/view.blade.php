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
                        <h4 class="d-flex">Books Detail
                            <a href="{{ url('lostbooks') }}" class="btn btn-cta ms-auto">Back</a>
                            <!--<a type="button" onclick="printQRCode('{{ $data->id }}','{{ $data->uid }}', '{{ $data->qrcode }}', '{{ $data->book_no }}')" class="btn btn-cta">Print QR</a>-->
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <div class="table-responsive">
                                    <table class="table">
                                         <div class="user-info">
                                            <tr>
                                                <td class="text-muted">Office: </td>
                                                <td>{{$data->office->name}}</td>
                                            </tr>
                                         </div>
                                        <tr>
                                            <td class="text-muted">Office Location: </td>
                                            <td>{{ $data->office->address ??''}}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="text-muted">Bookshelf Number :  </td>
                                            <td>{{ $data->bookshelves->number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Category : </td>
                                            <td>{{$data->category->name}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">UID : </td>
                                            <td>{{ $data->uid ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Title : </td>
                                            <td>{{ $data->title ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Author : </td>
                                            <td>{{ $data->author ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Publisher : </td>
                                            <td>{{ $data->publisher ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Edition : </td>
                                            <td>{{ $data->edition ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Pages : </td>
                                            <td>{{ $data->page ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Year : </td>
                                            <td>{{ $data->year ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Quantity : </td>
                                            <td>{{ $data->quantity ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Book No : </td>
                                            <td>{{ $data->book_no ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status : </td>
                                            <td>{{($data->status == 1) ? 'Active' : 'Inactive'}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created By: </td>
                                            <td>{{ $data->user->name ??'' }}</td>
                                        </tr>
                                        <!--<tr>-->
                                        <!--    <td class="text-muted">QR: </td>-->
                                        <!--    <td><img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$data->qrcode}}&height=6&textsize=10&scale=6&includetext" -->
                                        <!--                 alt="QR Code" -->
                                        <!--                 style="height: 105px;width:105px" -->
                                        <!--                 id="qr-{{$data->id}}" -->
                                        <!--                 data-qrcode="{{ $data->qrcode }}"></td>-->
                                        <!--</tr>-->
                                        <tr>
                                            <td class="text-muted">Created At: </td>
                                            <td>{{ date('d-m-Y', strtotime($data->created_at)) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                <div class="card-body" >
                        <table class="" style="display:none" id="print-code">
                                <tr>
                                    <td class="text-muted">Book Name :  </td>
                                    <td>{{ $data->title }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Qrcode: </td>
                                    <td><img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$data->qrcode}}&height=6&textsize=10&scale=6&includetext" alt="" style="height: 105px;width:105px" id="{{$data->qrcode}}"></td>
                                </tr>
                        </table>
                </div>
@endsection


@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- printThis Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
<script>

   $(document).ready(function() {
            $('#basic').on('click', function() {
                $('#print-code').show();
                $('#print-code').printThis({
                importCSS: true,        // Import page CSS
                importStyle: true,      // Import style tags
                loadCSS: "",            // Load an additional CSS file
                pageTitle: "Books Info", // Title for the printed document
                removeInline: false,    // Keep the inline styles
                printDelay: 333,        // Delay before printing to allow images to load
                afterPrint: function() {
                    $('#print-code').hide(); // Hide the table again after printing
                }
            });
            });
        });
        
        
        function printQRCode(itemId,uid, qrText,bookNo) {
        // Open a new window for printing
        const printWindow = window.open('', '', 'width=600,height=400');
        const qrSrc = `https://bwipjs-api.metafloor.com/?bcid=qrcode&text=${qrText}&height=6&textsize=10&scale=6&includetext`;

        printWindow.document.write(`
            <html>
            <head>
                <title>Print QR</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                    }
                    .sticker-container {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        justify-content:center;
                        width: 400px;  /* Adjust width to match sticker size */
                        height: 150px; /* Adjust height to match sticker size */
                        margin-top: 20px;
                    }
                    .print-container {
                        margin-top: 20px;
                    }
                    .book-title {
                        font-size: 20px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .qr-code {
                        width: 40%; /* QR code on the left half */
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position:relative;
                        top:10px;
                       
                    }
                    .uid-text {
                        width: 50%; /* UID on the right half */
                        font-size: 17px;
                        
                        position:relative;
                        top:10px;
                        line-height:1;
                        text-align:left;
                        
                    }
                        .uid-text h2 {
                            line-height:0;
                            
                        }
                        .uid-text h2.tt {
                            position:relative;
                            top:10px;
                        }
                   
                </style>
            </head>
            <body>
                <div class="sticker-container">
                    <!-- Book Name -->
                    
                    
                    <!-- QR Code Placeholder -->
                    <div class="qr-code">
                        <img id="qr-code-img" src="${qrSrc}" style="height: 80px; width: 80px;">
                    </div>
                     <div class="uid-text">
                        <img style="width:190px" src="{{asset('backend/images/logo.png')}}">
                        <h2>${bookNo}</h2>
                        <h2 class="tt">${uid}</h2>
                    </div>
                </div>
            </body>
            </html>
        `);

        // Wait for the image to load before printing
        const qrCodeImg = printWindow.document.getElementById('qr-code-img');
        qrCodeImg.onload = function () {
            // Once the image is loaded, trigger the print
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };

        // If the image fails to load, close the window
        qrCodeImg.onerror = function () {
            alert('QR code could not be loaded.');
            printWindow.close();
        };
    }
</script>
@endsection