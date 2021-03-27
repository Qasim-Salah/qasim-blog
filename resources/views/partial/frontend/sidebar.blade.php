<div class="wn__sidebar">
    <!-- Start Single Widget -->
    <aside class="widget search_widget">
        <h3 class="widget-title">Search</h3>
        {!! Form::open(['route'=>'frontend.search', 'method' => 'get']) !!}
        <div class="form-input">
            {!! Form::text('keyword', old('keyword', request()->keyword), ['placeholder' => 'Search...']) !!}
            {!! Form::button('<i class="fa fa-search"></i>', ['type' => 'submit']) !!}
        </div>
        {!! Form::close() !!}
    </aside>
    <!-- End Single Widget -->

    <!-- Start Single Widget -->
    <aside class="widget recent_widget">
        <h3 class="widget-title">Recent Posts</h3>
        <div class="recent-posts">
            <ul>
{{--                @foreach($recent_posts as $recent_post)--}}
                    <li>
                        <div class="post-wrapper d-flex">
                            <div class="thumb">
                                <a href="#">
                                        <img src="{{ asset('assets/posts/default_small.jpg') }}" alt="blog images">
                                </a>
                            </div>
                            <div class="content">
                                <h4>
                                    <a href="#"></a>
                                </h4>
{{--                                <p>    {{ $recent_post->created_at->format('M d, Y') }}</p>--}}
                            </div>
                        </div>
                    </li>
{{--                @endforeach--}}
            </ul>
        </div>
    </aside>
    <!-- End Single Widget -->

    <!-- Start Single Widget -->
    <aside class="widget comment_widget">
        <h3 class="widget-title">Comments</h3>
        <ul>
{{--            @foreach($recent_comments as $recent_comment)--}}
                <li>
                    <div class="post-wrapper">
                        <div class="thumb">
                            <img src="#" alt="#">
                        </div>
                        <div class="content">
                            <p> says:</p>
                            <a href="#"></a>
                        </div>
                    </div>
                </li>
{{--            @endforeach--}}
        </ul>
    </aside>
    <!-- End Single Widget -->

    <!-- Start Single Widget -->
    <aside class="widget category_widget">
        <h3 class="widget-title">Categories</h3>
        <ul>
{{--            @foreach(App\Models\Category::get() as $global_category)--}}
                <li><a href=""></a></li>
{{--            @endforeach--}}
        </ul>
    </aside>
    <!-- End Single Widget -->

    <!-- Start Single Widget -->
    <aside class="widget archives_widget">
        <h3 class="widget-title">Archives</h3>
        <ul>
{{--            @foreach($global_archives as $key => $val)--}}
                <li><a href="#">#</a></li>
{{--            @endforeach--}}
        </ul>
    </aside>
    <!-- End Single Widget -->
</div>
