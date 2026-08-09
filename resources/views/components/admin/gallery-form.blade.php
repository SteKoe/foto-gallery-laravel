<h3 class="h3">{{ $gallery ? $gallery['name'] : 'Global' }}</h3>

<div class="checkbox-group">
    @foreach ($galleryImageTags as $galleryImageTag)
    @php
    $idx = mt_rand(0, 9999);
    $isInherited = $gallery && in_array($galleryImageTag->tag_id, $globalCheckedTagIds ?? []);
    @endphp

    <x-form.checkbox
        :idx="$idx"
        :label="$galleryImageTag->tag_value"
        :value="$galleryImageTag->tag_id"
        :disabled="$isInherited || in_array($galleryImageTag->tag_id, [9])"
        :checked="$isInherited || in_array($galleryImageTag->tag_id, array_map(fn ($tag) => $tag['tag_id'], $checkedTags))"
        :name="$isInherited ? null : 'tag[' . ($gallery ? $gallery['slug'] : 'global') . '][]'"
        :title="$isInherited ? __('messages.tag_inherited') : null"
    />
    @endforeach
</div>
