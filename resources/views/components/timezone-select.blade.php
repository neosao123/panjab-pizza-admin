<select name="{{ $name }}" id="{{ $name }}" class="form-control mb-2">
  <option value="">Select a timezone</option>
  @foreach ($timezones as $timezone)
    <option value="{{ $timezone }}" {{ $timezone == $selected ? 'selected' : '' }}>
      {{ $timezone }}
    </option>
  @endforeach
</select>
