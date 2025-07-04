import React, { useEffect, useRef, useState } from 'react'
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import docsearch from 'meilisearch-docsearch'
import 'meilisearch-docsearch/css'
import './styles.css'

// Fallback search button component
function FallbackSearchButton () {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const openSearchModal = () => {
    // Try to find and click the actual docsearch button if it exists
    const docsearchButton = document.querySelector('#docsearch button')
    if (docsearchButton) {
      docsearchButton.click()
      return
    }

    // Otherwise show a message
    setIsModalOpen(true)
    setTimeout(() => setIsModalOpen(false), 3000)
  }

  return (
    <>
      <button
        className='fallback-search-button'
        onClick={openSearchModal}
        aria-label='Search'
      >
        🔍 Search
      </button>
      {isModalOpen && (
        <div className='fallback-search-modal'>
          Search is currently unavailable. Please try again later.
        </div>
      )}
    </>
  )
}

export default function SearchBar () {
  const docsearchRef = useRef(null)
  const destroyRef = useRef(null)
  const [searchInitialized, setSearchInitialized] = useState(false)
  const [initializationError, setInitializationError] = useState(false)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const {siteConfig} = useDocusaurusContext();

  useEffect(() => {
    // Only run on client-side
    if (typeof window !== 'undefined') {
      // Add a delay to ensure the DOM is fully loaded
      const initializeSearch = setTimeout(() => {
        if (docsearchRef.current) {
          try {
            // Make sure any existing Algolia elements are removed
            const algoliaElements = document.querySelectorAll('.DocSearch-Button')
            algoliaElements.forEach(el => el.remove())

            // Force the container to be empty
            docsearchRef.current.innerHTML = ''

            // Initialize Meilisearch with the directly imported docsearch function
            const destroy = docsearch({
              host: `${siteConfig.customFields.meilisearchUrl}`,
              apiKey: `${siteConfig.customFields.meilisearchApiKey}`,
              indexUid: `${siteConfig.customFields.meilisearchIndexUid}`,
              container: '#docsearch',
            })

            destroyRef.current = destroy
            setSearchInitialized(true)

            // Force the button to be visible by adding a class to the container
            docsearchRef.current.classList.add('meilisearch-docsearch-container')

            // Create a button if none exists after initialization
            setTimeout(() => {
              const button = document.querySelector('#docsearch button')
              if (!button) {
                const searchButton = document.createElement('button')
                searchButton.textContent = '🔍 Search'
                searchButton.className = 'meilisearch-docsearch-button'

                // Add click event to open the search modal
                searchButton.addEventListener('click', () => {
                  // Try to find and click any existing docsearch button
                  const existingButton = document.querySelector('.DocSearch-Button')
                  if (existingButton) {
                    existingButton.click()
                    return
                  }

                  // If no button exists, show a fallback modal
                  setIsModalOpen(true)
                  // Automatically close the modal after 3 seconds
                  setTimeout(() => setIsModalOpen(false), 3000)
                })

                docsearchRef.current.appendChild(searchButton)
              }
            }, 500)
          } catch (error) {
            console.error('[ERROR] Failed to initialize Meilisearch DocSearch:', error)
            setInitializationError(true)
          }
        }
      }, 300)

      return () => {
        clearTimeout(initializeSearch)
        if (destroyRef.current) {
          destroyRef.current()
          destroyRef.current = null
        }
      }
    }
  }, [])

  // Check if the docsearch button is actually rendered after a short delay
  useEffect(() => {
    if (searchInitialized) {
      const timer = setTimeout(() => {
        const docsearchButton = document.querySelector('#docsearch button')
        if (!docsearchButton) {
          console.warn('[WARN] DocSearch button not found in DOM after initialization')

          // Try to find any button with docsearch-related classes
          const anyDocsearchButton = document.querySelector('.DocSearch-Button, .meilisearch-docsearch-button')
          if (!anyDocsearchButton) {
            console.warn('[WARN] No search button found in DOM, falling back to error state')
            setInitializationError(true)
          }
        }
      }, 1000)

      return () => clearTimeout(timer)
    }
  }, [searchInitialized])

  // Additional check to ensure the search box is visible after the component mounts
  useEffect(() => {
    if (typeof window !== 'undefined') {
      const visibilityCheck = setTimeout(() => {
        const searchContainer = document.querySelector('#docsearch')
        const searchButton = document.querySelector('#docsearch button, .DocSearch-Button, .meilisearch-docsearch-button')

        // If the container exists but the button doesn't, or the button is not visible, try to fix it
        if (searchContainer && (!searchButton || window.getComputedStyle(searchButton).display === 'none')) {
          // Force the container to be visible
          searchContainer.style.display = 'block'
          searchContainer.style.visibility = 'visible'
          searchContainer.style.opacity = '1'

          // If no button exists, create one
          if (!searchButton) {
            const newButton = document.createElement('button')
            newButton.textContent = '🔍 Search'
            newButton.className = 'meilisearch-docsearch-button'

            // Add click event to the manually created button
            newButton.addEventListener('click', () => {
              // Try to find and click any existing docsearch button
              const existingButton = document.querySelector('.DocSearch-Button')
              if (existingButton) {
                existingButton.click()
                return
              }

              // If no button exists, show the fallback modal
              setIsModalOpen(true)
              setTimeout(() => setIsModalOpen(false), 3000)
            })

            searchContainer.appendChild(newButton)
          } else {
            // Force the button to be visible
            searchButton.style.display = 'flex'
            searchButton.style.visibility = 'visible'
            searchButton.style.opacity = '1'
          }
        }
      }, 2000)

      return () => clearTimeout(visibilityCheck)
    }
  }, [])

  return (
    <div className='search-bar-container'>
      {initializationError ? (
        <div className='search-bar-fallback'>
          <FallbackSearchButton />
        </div>
      ) : (
        <div ref={docsearchRef} id='docsearch' className='search-bar' />
      )}
      {isModalOpen && (
        <div className='fallback-search-modal'>
          Search is currently unavailable. Please try again later.
        </div>
      )}
    </div>
  )
}
