<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WMS')</title>

    <link rel="stylesheet" href="{{ asset('css/wms/wms.css') }}">
</head>
<body>
<div class="app">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar__brand">
            <div class="brand__logo">ロジクラ<br><span class="brand__sub">Clone</span></div>
        </div>

        <nav class="sidebar__nav">
            <div class="nav__section">商品</div>
            <a class="nav__link {{ request()->is('products*') ? 'is-active' : '' }}" href="{{ route('products.index') }}">
                商品
            </a>

            <div class="nav__section">在庫</div>
            <a class="nav__link {{ request()->is('stocks*') ? 'is-active' : '' }}" href="#">
                在庫（商品単位）
            </a>
            <a class="nav__link {{ request()->is('locations*') ? 'is-active' : '' }}" href="#">
                保管場所
            </a>

            <div class="nav__section">取引先</div>
            <a class="nav__link {{ request()->is('suppliers*') ? 'is-active' : '' }}" href="{{ route('suppliers.index') }}">
                仕入先
            </a>
            <a class="nav__link {{ request()->is('customers*') ? 'is-active' : '' }}" href="{{ route('customers.index') }}">
                出荷先
            </a>
        </nav>

        <div class="sidebar__footer">
            <div class="mini">ログイン中：admin</div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar__left">
                <button class="icon-btn" type="button" aria-label="menu">☰</button>
                <div class="topbar__title">@yield('header_title', '商品')</div>
            </div>

            <div class="topbar__right">
                <a class="topbar__link" href="#">スタートガイド</a>
                <a class="topbar__link" href="#">ヘルプ</a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="topbar__logout" type="submit">ログアウト</button>
                </form>
            </div>
        </header>

        {{-- Content --}}
        <main class="content">
            @if (session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert--danger">
                    <div class="alert__title">入力に誤りがあります</div>
                    <ul class="alert__list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>
</body>
</html>
