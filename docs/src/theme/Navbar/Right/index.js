import React from 'react'
import NavbarRight from '@theme-original/Navbar/Right'
import './styles.css'

export default function NavbarRightWrapper (props) {
  return (
    <div className='navbar-right-wrapper'>
      <NavbarRight {...props} />
    </div>
  )
}
