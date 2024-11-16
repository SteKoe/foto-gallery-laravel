<h3 class="h3">{{ $gallery ? $gallery['name'] : "Global" }}</h3>

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
        name="tag[{{ $gallery ? $gallery['slug'] : 'global' }}]"
    />
    @endforeach
</div>

