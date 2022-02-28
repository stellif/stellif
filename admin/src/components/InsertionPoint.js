export default function InsertionPoint({
  open,
  blocks,
  onOpen,
  onAdd,
  onClose,
}) {
  return (
    <div className="insertion-point">
      {open && (
        <div className="menu">
          {blocks.map((block, index) => (
            <button
              key={index}
              className="item"
              onClick={() => onAdd(block.identifier)}
            >
              {block.name}
            </button>
          ))}
        </div>
      )}
      <button onClick={onOpen}>Open menu</button>
    </div>
  );
}
