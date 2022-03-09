import '../css/page.css';

export default function Page({className, children}) {
    return (
        <div className={'page ' + className}>
            {children}
        </div>
    );
}