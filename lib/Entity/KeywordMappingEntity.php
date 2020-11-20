<?php

namespace  OCA\Cookbook\Entity;

use OCA\Cookbook\Db\KeywordMappingDbWrapper;

class KeywordMappingEntity implements Entity {
	
	/**
	 * @var RecipeEntity
	 */
	private $recipe;
	
	/**
	 * @var KeywordEntity
	 */
	private $keyword;
	
	/**
	 * Create a new category entity
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param KeywordMappingDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(KeywordMappingDbWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
	}
	/**
     * @return \OCA\Cookbook\Entity\RecipeEntity
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

	/**
     * @return \OCA\Cookbook\Entity\KeywordEntity
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

	/**
     * @param \OCA\Cookbook\Entity\RecipeEntity $recipe
     */
    public function setRecipe($recipe)
    {
        $this->recipe = $recipe;
    }

	/**
     * @param \OCA\Cookbook\Entity\KeywordEntity $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }
}
