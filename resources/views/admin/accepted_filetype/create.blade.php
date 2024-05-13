@extends('layouts.admin.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Τύποι αρχείων που γίνονται δεκτοί στις φόρμες</div>

                    <div class="card-body">
                        <p>
                        Σημείωση: Οι τύποι που θα προσθέσετε σιγουρευτείτε ότι γίνονται δεκτοί από τον διακομιστή ιστού που χρησιμοποιείτε
                        καθώς μπορεί να υπάρχει απαγόρευση για συγκεκριμένους τύπους αρχείων. Στο πλαίσιο των επεκτάσεων μπορείτε να προσθέσετε
                        παραπάνω από μια επέκταση χωρίζοντάς τες με κόμμα πχ. .jpg,.png,.wav
                        </p>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div><br />
                        @endif
                        <form action={{ route('admin.accepted_filetype.store') }} method='post'>

                            <div class="form-group row mb-3">
                                <label for="description" class="col-md-4 col-form-label text-md-right">Περιγραφή</label>

                                <div class="col-md-6">
                                    <input id="description" type="text"
                                        class="form-control @error('description') is-invalid @enderror" name="description"
                                        value="{{ old('description') }}" required autocomplete="description" autofocus required>

                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="extension" class="col-md-4 col-form-label text-md-right">Επεκτάσεις</label>

                                <div class="col-md-6">
                                    <input id="extension" type="text"
                                        class="form-control @error('extension') is-invalid @enderror" name="extension"
                                        value="{{ old('extension') }}" required autocomplete="extension" autofocus required>

                                    @error('extension')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <div class="col-2">
                                    <a class="btn btn-danger" href="{{ route('admin.accepted_filetype.index') }}">Ακύρωση</a>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
                                </div>
                            </div>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
