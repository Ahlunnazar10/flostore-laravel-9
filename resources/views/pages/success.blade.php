@extends('layouts.app')

@section('title')
    Store Success Page
@endsection

@section('content')
    <!-- Register Content -->
    <div class="page-content page-auth" id="register">
        <div class="section-store-auth" data-aos="fade-up">
            <div class="container">
                <div class="row align-items-center justify-content-center row-login">
                    <div class="col-lg-4">
                        <h2>
                            Memulai untuk jual beli <br />
                            dengan cara terbaru
                        </h2>
                        <form class="mt-3">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" class="form-control is-valid" v-model="name" autofocus />
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control is-invalid" v-model="email" autofocus />
                            </div>
                            <div class="form-group">
                                <label for="">Password</label>
                                <input type="password" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label for="">Store</label>
                                <p class="text-muted">Apakah anda juga ingin membuka toko?</p>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" name="is_store_open"
                                        id="openStoreTrue" v-model="is_store_open" :value="true" />
                                    <label for="openStoreTrue" class="custom-control-label">Iya, boleh</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" name="is_store_open"
                                        id="openStoreFalse" v-model="is_store_open" :value="false" />
                                    <label for="openStoreFalse" class="custom-control-label">Enggak, makasih</label>
                                </div>
                            </div>
                            <div class="form-group" v-if="is_store_open">
                                <label>Nama Toko</label>
                                <input type="text" class="form-control" />
                            </div>
                            <div class="form-group" v-if="is_store_open">
                                <label>Kategori</label>
                                <select name="category" class="form-control">
                                    <option value="" desabled>Select Category</option>
                                </select>
                            </div>

                            <button class="btn btn-success btn-block mt-4">
                                Sign Up Now
                            </button>
                            <button class="btn btn-signup btn-block mt-2">
                                Back to Sign In
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
