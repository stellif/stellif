export default function ParagraphBlock({ defaultValue, onChange }) {
  return (
    <textarea
      defaultValue={defaultValue}
      onInput={(e) => onChange(e.target.value)}
    ></textarea>
  );
}
