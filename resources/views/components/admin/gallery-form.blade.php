<form class="form--admin" name="edit-user-form" id="edit-user-form" method="post"
      action="{{ url('/admin/user/' . $user->user_id) }}/permissions">
    @csrf

    <h3 class="h3">{{ $gallery ? $gallery['name'] : "Global" }}</h3>

    <input type="hidden" name="gallery_slug" value="{{ $gallery ? $gallery['slug'] : null }}">
    <div class="checkbox-group">
        @foreach ($galleryImageTags as $galleryImageTag)
        @php
        $idx = mt_rand(0, 9999);
        @endphp

        <x-form.checkbox
            :idx="$idx"
            :label="$galleryImageTag->tag_value"
            :value="$galleryImageTag->tag_id"
            :disabled="in_array($galleryImageTag->tag_id, [9])"
            :checked="in_array($galleryImageTag->tag_id, array_map(function($tag) { return $tag['tag_id']; }, $checkedTags))"
            name="tag[]"
        />
        @endforeach
    </div>

    <div class="btn-group">
        <button type="submit" class="btn btn-primary btn-small">Save</button>
    </div>
</form>
